import qs from "qs";

export default async function makeIglooRequest(el: HTMLElement, props: Record<string, any> = {})
{
    if (!el.dataset.iglooAction && !props.action) {
        return;
    }

    let body = undefined;
    let method = 'GET';
    let url = undefined;
    let query = undefined;

    if (el.dataset.iglooActionBody) {
        body = JSON.parse(el.dataset.iglooActionBody);
    }
    if (props.body) {
        body = {...(body || {}), ...props.body}
    }

    const action = el.dataset.iglooAction || props.action;
    if (action) {
        method = 'POST';
        url = Craft.getCpUrl()
        body = {
            [Craft.csrfTokenName]: encodeURIComponent(Craft.csrfTokenValue),
            action,
            ...(body || {}),
        }
    }

    if (el.dataset.iglooActionQuery) {
        query = JSON.parse(el.dataset.iglooActionQuery)
    }
    if (props.query) {
        query = {...(query || {}), ...props.query}
    }

    if (url) {
        const response = await (await fetch(url+'&'+qs.stringify(query), {
            body: qs.stringify(body),
            method,
            headers: {
                'content-type': 'application/x-www-form-urlencoded',
                'accept': 'application/json',
            }
        })).json();
    }
}
