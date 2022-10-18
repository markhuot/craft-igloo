import qs from 'qs';
import './menu';

type IglooResponseDomAction = {
    action: 'insert' | 'remove' | 'replace';
    scope: string;
    position: 'beforebegin' | 'afterbegin' | 'beforeend' | 'afterend';
    html: string;
};

type IglooResponseEvent = {
    name: string;
    detail: Record<string, any>;
}

type IglooResponseBody = {
    message: string;
    domActions?: IglooResponseDomAction[];
    events?: IglooResponseEvent[];
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
            const scopes = document.querySelectorAll(action.scope);
            if (scopes.length) {
                if (action.action === 'insert') {
                    scopes.forEach(s => s.insertAdjacentHTML(action.position, action.html));
                }
                if (action.action === 'remove') {
                    scopes.forEach(s => s.remove());
                }
                if (action.action === 'replace') {
                    scopes.forEach(s => {
                        s.insertAdjacentHTML('afterend', action.html);
                        s.remove();
                    });
                }
            }
        })
    }

    if (response?.events) {
        response.events.forEach(event => {
            if (event.name === 'createDraft' && event.detail?.provisional) {
                $('#main-form').data('elementEditor').initForProvisionalDraft();
            }
            if (event.name === 'markChanged') {
                $('#main-form').data('elementEditor')?.preview?.$refreshBtn?.trigger('click');
                // .prepend($("<div/>", {
                //     class: "status-badge modified",
                //     title: Craft.t("app", "This field has been modified.")
                // }).append($("<span/>", {
                //     class: "visually-hidden",
                //     html: Craft.t("app", "This field has been modified.")
                // })
            }
        })
    }
}

document.addEventListener('click', async (event) => {
    let body = undefined;
    let method = 'GET';
    let url = undefined;
    let query = undefined;

    // Look up the tree in case the "target" is an element _inside_ the actual
    // data-igloo- element we're actually concerned with
    let target = event.target! as HTMLElement|null;
    while (target && !target.dataset.iglooSlideout && !target.dataset.iglooAction) {
        target = target.parentElement;
    }
    if (!target) {
        return;
    }

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

    if (target.dataset.iglooActionQuery) {
        query = JSON.parse(target.dataset.iglooActionQuery)
    }

    if (target.dataset.iglooSlideout !== undefined) {
        const url = target.dataset.iglooSlideout || target.getAttribute('href');
        if (url) {
            addSlideout(url, callerData);

            event.preventDefault()
            return false;
        }
    }

    if (url) {
        event.preventDefault()

        const response = await (await fetch(url+'&'+qs.stringify(query), {
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
