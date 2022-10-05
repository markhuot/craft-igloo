declare class CpScreenSlideout {
    constructor(url: string, settings: object = {});
    close();
}
declare class ElementEditorSlideout {
    constructor(element: object, settings: object = {});
}

type Craft = {
    cp: any;
    getCpUrl(uri?: string, options?: object);
    csrfTokenName: string;
    csrfTokenValue: string;
    CpScreenSlideout;
    ElementEditorSlideout;
};

declare var Craft: Craft;
