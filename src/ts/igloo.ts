import qs from 'qs';

type IglooResponseDomAction = {
    action: 'insert' | 'remove';
    scope: string;
    position: 'beforebegin' | 'afterbegin' | 'beforeend' | 'afterend';
    html: string;
};

type IglooResponseBody = {
    message: string;
    domActions?: IglooResponseDomAction[];
    cpEditUrl?: string;
    entry?: any;
};

type IglooCallerData = {
    slideoutAction?: 'replace';
}

const layers: CpScreenSlideout[] = [];

function addSlideout(url: string, callerData: IglooCallerData) {
    const slideout = new Craft.CpScreenSlideout(url);

    if (callerData.slideoutAction === 'replace' && layers.length) {
        layers[layers.length-1].close();
    }
    layers.push(slideout);

    slideout.on('submit', (ev: { response: { data: IglooResponseBody }}) => {
        handleResponse(ev.response.data, callerData, true);
    });

    slideout.on('close', () => {
        // ...
    });
}

function handleResponse(response: IglooResponseBody, callerData: IglooCallerData, muteMessages = false) {
    if (response.message && !muteMessages) {
        Craft.cp.displayNotice(response.message);
    }

    // @todo, this is magic, we need a better heuristic to control nested slideouts
    if (response.cpEditUrl) {
        const entry = response.entry;

        if (callerData.slideoutAction === 'replace' && layers.length) {
            layers[layers.length - 1].close();
        }

        layers.push(new Craft.ElementEditorSlideout(entry, {
            draftId: entry.draftId,
            elementId: entry.id,
            elementType: 'craft\\elements\\Entry',
            params: { fresh: 1 },
            prevalidate: false,
            revisionId: null,
            saveParams: {},
            showHeader: true,
            siteId: entry.siteId,
            validators: [],
        }));
    }

    if (response?.domActions) {
        response.domActions.forEach(action => {
            const scope = document.querySelector(action.scope);
            if (scope) {
                if (action.action === 'insert') {
                    scope.insertAdjacentHTML(action.position, action.html)
                }
                if (action.action === 'remove') {
                    scope.remove();
                }
            }
        })
    }
}

document.addEventListener('click', async (event) => {
    let body = undefined;
    let method = 'GET';
    let url = undefined;
    const target = event.target! as HTMLElement;
    const callerData = {
        slideoutAction: target.dataset.iglooSlideoutAction as IglooCallerData['slideoutAction'],
    }

    if (target.dataset.iglooActionData) {
        body = JSON.parse(target.dataset.iglooActionData);
    }

    if (target.dataset.iglooAction) {
        method = 'POST';
        url = Craft.getCpUrl()
        body = {
            [Craft.csrfTokenName]: encodeURIComponent(Craft.csrfTokenValue),
            action: target.dataset?.iglooAction,
            ...(body || {}),
        }
    }

    if (target.dataset.iglooSlideout) {
        addSlideout(target.dataset.iglooSlideout, callerData);

        event.preventDefault()
        return false;
    }

    if (url) {
        const response = await (await fetch(url, {
            body: qs.stringify(body),
            method,
            headers: {
                'content-type': 'application/x-www-form-urlencoded',
                'accept': 'application/json',
            }
        })).json() as IglooResponseBody;

        handleResponse(response, callerData);
    }
})
