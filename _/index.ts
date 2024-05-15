// =============================================================================
// Der Index von allem Frontend Code.
// Webpack generiert daraus den Ordner `jsbuild/olz`.
// =============================================================================

import lightGallery from 'lightgallery';
import lgVideo from 'lightgallery/plugins/video';

import './index.scss';

export * from '../src/Apps/index';
export * from '../src/Components/index';
export * from '../src/Karten/index';
export * from '../src/News/index';
export * from '../src/Roles/index';
export * from '../src/Service/index';
export * from '../src/Snippets/index';
export * from '../src/Startseite/index';
export * from '../src/Termine/index';
export * from '../src/Utils/index';

/* @ts-ignore: Ignore file is not a module. */
import * as bootstrap from 'bootstrap';
import $ from 'jquery';
import 'jquery-ui/ui/widgets/datepicker';

window.bootstrap = bootstrap;

// OLZ library (as generated by webpack, i.e. all exports of this file)
declare const olz: {[key: string]: unknown};

export function loaded(): void {
    // TODO: remove
    /* @ts-expect-error: Ignore type unsafety. */
    window.MailTo = olz.MailTo;

    $(() => {
        const lightGalleryElems = document.querySelectorAll('.lightgallery');
        for (let i = 0; i < lightGalleryElems.length; i++) {
            lightGallery(lightGalleryElems[i] as HTMLElement, {
                hideControlOnEnd: true,
                plugins: [lgVideo],
                speed: 500,
                selector: 'a[data-src]',
            });
        }
        $.datepicker.setDefaults({
            dateFormat: 'yy-mm-dd',
        });
        $('.datepicker').datepicker();
    });
    console.log('OLZ loaded!');
}

/* EMAILADRESSE MASKIEREN (global) */
export function MailTo(name: string, domain: string, text: string, subject = ''): string {
    // eslint-disable-next-line no-useless-concat
    const protocol = ('mail' + 'to:');
    const mytext = `<a href="${protocol}${name}@${domain}?subject=${subject}" class="linkmail">${text}</a>`;
    const scriptElement = document.currentScript;
    const parentNode = scriptElement?.parentNode;
    if (!scriptElement || !parentNode) {
        return mytext;
    }
    if (/MailTo\(/.exec(scriptElement.innerHTML)) {
        const span = document.createElement('span');
        parentNode.insertBefore(span, scriptElement);
        span.innerHTML = mytext;
        return '';
    }
    return mytext;
}
