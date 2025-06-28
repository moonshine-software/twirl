import {HtmlReloadActions} from "../Enums/HtmlReloadActions";

export interface TwirlPayload {
    event: string,
    data: TwirlData
}

interface TwirlData {
    selector: string,
    action: HtmlReloadActions,
    html: string,
}