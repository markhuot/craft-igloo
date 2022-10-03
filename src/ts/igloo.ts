import qs from 'qs';

type IglooResponseBodyActions = {
    remove?: Array<string>;
    insert?: Array<string>;
};

type IglooResponseBody = {
    message: string;
    domActions?: IglooResponseBodyActions;
};

function handleResponse(response: IglooResponseBody) {
    if (response.message) {
        Craft.cp.displayNotice(response.message);
    }

    if (response?.domActions?.remove) {
        response.domActions.remove.map(selector => {
            const el = document.querySelector(selector);
            el?.parentNode?.removeChild(el);
        })
    }

    if (response?.domActions?.insert) {
        response.domActions.insert.forEach(html => {
            console.log(document.querySelector('[data-slot]'))
            document.querySelector('[data-slot]')?.insertAdjacentHTML('beforeend', html);
        })
    }
}

document.addEventListener('click', async (event) => {
    let body = undefined;
    let method = 'GET';
    let url = undefined;
    const target = event.target as HTMLElement;

    if (target.dataset.iglooActionData) {
        body = JSON.parse(target.dataset.iglooActionData);
    }

    if (target.dataset.iglooAction) {
        method = 'POST';
        url = Craft.getCpUrl()
        body = {
            [Craft.csrfTokenName]: encodeURIComponent(Craft.csrfTokenValue),
            action: event.target.dataset.iglooAction,
            ...(body || {}),
        }
    }

    if (target.dataset.iglooSlideout) {
        url = target.dataset.iglooSlideout;
        const slideout = new Craft.CpScreenSlideout(url);

        slideout.on('submit', ev => {
            handleResponse(ev.data);
        });

        slideout.on('close', () => {
            // ...
        });

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

        handleResponse(response);
    }
})
