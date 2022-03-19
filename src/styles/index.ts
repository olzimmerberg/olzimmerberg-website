import lightGallery from 'lightgallery';
import lgVideo from 'lightgallery/plugins/video';

/* @ts-ignore: Ignore file is not a module. */
export * from 'bootstrap';
/* @ts-expect-error: Ignore file is not a module. */
export * from 'jquery';
/* @ts-ignore: Ignore file is not a module. */
export * from 'jquery-ui/ui/widgets/datepicker';

import 'lightgallery/css/lightgallery.css';
import 'lightgallery/css/lg-video.css';
import 'jquery-ui/themes/base/theme.css';
import 'jquery-ui/themes/base/datepicker.css';

import 'typeface-open-sans';

import './bootstrap.scss';
import './dropzone.scss';
import './styles.scss';

export function loaded(): void {
    /* @ts-expect-error: Ignore type unsafety. */
    // eslint-disable-next-line dot-notation
    window['$'] = $;

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

setTimeout(() => {
    loaded();
}, 1000);
