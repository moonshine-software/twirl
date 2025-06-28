import {Events} from "./Twirl/Enums/Events";
import {TwirlPayload} from "./Twirl/Types/TwirlPayload";
import {HtmlReloadActions} from "./Twirl/Enums/HtmlReloadActions";

document.addEventListener("moonshine:twirl", (): void => {
    const MoonShine: MoonShine = window.MoonShine

    const htmlElementData = document.getElementById('twirl-html-reload')
    if(htmlElementData === null) {
        return;
    }

    const channel = htmlElementData.getAttribute('data-channel')
    if(channel === null) {
        return;
    }

    if(! MoonShine.callbacks['onTwirl']) {
        return;
    }

    MoonShine.callbacks['onTwirl'](
        channel,
        (payload: TwirlPayload): void => {
            switch(payload.event) {
                case Events.TWIRL:
                    const htmlElement = document.querySelectorAll(payload.data.selector)
                    if(htmlElement.length === 0) {
                        return
                    }

                    for(let i = 0; i < htmlElement.length; i++) {
                        if(payload.data.action === HtmlReloadActions.INNER_HTML) {
                            htmlElement[i].innerHTML = payload.data.html
                            continue
                        }

                        if(payload.data.action === HtmlReloadActions.OUTER_HTML) {
                            htmlElement[i].outerHTML = payload.data.html
                            continue
                        }

                        htmlElement[i].insertAdjacentHTML(payload.data.action, payload.data.html)
                    }

                    break
            }
        },
    )
})