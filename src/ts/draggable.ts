import doIglooAction from "./action";
import makeIglooRequest from "./action";

interface IglooElement extends HTMLElement {
    iglooProxy: HTMLElement;
}

document.addEventListener('dragstart', function (event) {
    const el = event.target as IglooElement;
    event.dataTransfer!.dropEffect = 'move';

    // log that we're dragging, so we can get it during the dragover event
    el.setAttribute('data-igloo-dragging', '');

    // Get the starting drag position
    el.dataset.dragStartTop = String(el.getBoundingClientRect().top);
    el.dataset.dragStartLeft = String(el.getBoundingClientRect().left);
    el.dataset.dragStartWidth = String(el.getBoundingClientRect().width);
    el.dataset.dragStartHeight = String(el.getBoundingClientRect().height);

    // Get mins and maxes
    const draggableContainer = el.closest('[data-igloo-draggable-container]') as HTMLElement|undefined;
    const minTop = draggableContainer?.getBoundingClientRect().top;
    const maxTop = minTop ? minTop + draggableContainer?.getBoundingClientRect().height : undefined;
    const minLeft = draggableContainer?.getBoundingClientRect().left;
    const maxLeft = minLeft ? minLeft + draggableContainer?.getBoundingClientRect().width - el.getBoundingClientRect().width : undefined;
    el.dataset.dragMinTop = String(minTop);
    el.dataset.dragMaxTop = String(maxTop);
    el.dataset.dragMinLeft = String(minLeft);
    el.dataset.dragMaxLeft = String(maxLeft);

    // create a proxy element to show the drag position
    const sourceEl = el.closest('[data-igloo-draggable]') as HTMLElement;
    const clone = sourceEl.cloneNode(true) as HTMLElement;
    clone.querySelectorAll('[draggable]').forEach(e => e.remove());
    clone.style.position = 'static';
    clone.style.top = '0px';
    clone.style.left = '0px';
    clone.style.width = '100%';
    clone.style.height = '100%';
    const container = document.createElement('div');
    container.style.position = 'absolute';
    container.style.width = sourceEl.getBoundingClientRect().width + 'px';
    container.style.height = sourceEl.getBoundingClientRect().height + 'px';
    container.appendChild(clone);
    document.body.appendChild(container);

    // store the proxy object on the dragged element so we can access it more easily
    el.iglooProxy = container;
});

document.body.ondragover = function (event) {
    event.preventDefault();
    event.dataTransfer!.dropEffect = 'move';

    document.querySelectorAll<IglooElement>('[data-igloo-dragging]').forEach(el => {
        // Get mins and maxes
        const minTop = el.dataset.dragMinTop ? parseInt(el.dataset.dragMinTop) : undefined;
        const maxTop = el.dataset.dragMaxTop ? parseInt(el.dataset.dragMaxTop) : undefined;
        const minLeft = el.dataset.dragMinLeft ? parseInt(el.dataset.dragMinLeft) : undefined;
        const maxLeft = el.dataset.dragMaxLeft ? parseInt(el.dataset.dragMaxLeft) : undefined;
        const totalWidth = maxLeft && minLeft ? maxLeft - minLeft : undefined;

        // Figure out where we started in case we're constrained
        const dragStartTop = parseInt(el.dataset.dragStartTop!);
        const dragStartLeft = parseInt(el.dataset.dragStartLeft!);
        const dragStartWidth = parseInt(el.dataset.dragStartWidth!);
        const dragStartHeight = parseInt(el.dataset.dragStartHeight!);

        // Figure out what axis we're allowed to move in
        const allowedAxis = (el.iglooProxy.firstElementChild as HTMLElement)?.dataset.iglooDraggableAxis || 'xy';
        const canMoveX = allowedAxis.includes('x');
        const canMoveY = allowedAxis.includes('y');

        // Figure out the desired placement
        let desiredTop = canMoveY ? event.clientY - (dragStartHeight / 2) : dragStartTop;
        let desiredLeft = canMoveX ? event.clientX - (dragStartWidth / 2) : dragStartLeft;

        // Figure out if we're snapping
        const snap = parseFloat((el.iglooProxy.firstElementChild as HTMLElement)?.dataset.iglooDraggableSnap || '0');
        if (snap > 0 && canMoveX && minLeft && maxLeft && totalWidth) {
            const percentLeft = (desiredLeft - minLeft) / totalWidth;
            const totalSteps = 1 / snap;
            const snappedPercentLeft = Math.round(totalSteps * percentLeft) / totalSteps;
            desiredLeft = (snappedPercentLeft * totalWidth) + minLeft;
        }

        // Clamp our values to the min/max if they exist
        const top = minTop && maxTop ? Math.max(minTop, Math.min(maxTop, desiredTop)) : desiredTop;
        const left = minLeft && maxLeft ? Math.max(minLeft, Math.min(maxLeft, desiredLeft)) : desiredLeft;

        // Now that we have a definitive value, set it
        el.iglooProxy.style.top = String(top) + 'px';
        el.iglooProxy.style.left = String(left) + 'px';
        el.dataset.positioning = JSON.stringify({
            top,
            left,
            minTop,
            maxTop,
            minLeft,
            maxLeft,
        });
    });
};

document.body.ondrop = function (event) {
    event.preventDefault();

    document.querySelectorAll<IglooElement>('[data-igloo-dragging]').forEach(el => {
        el.iglooProxy.remove();
        const positioning = JSON.parse(el.dataset.positioning!);

        const container = el.closest('[data-igloo-draggable]') as HTMLElement;
        makeIglooRequest(container, {
            body: positioning,
        }).then();
    });
};
