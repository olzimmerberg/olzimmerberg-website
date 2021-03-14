// =============================================================================
// Der Index von allem Frontend Code.
// Webpack generiert daraus den Ordner `jsbuild/`.
// =============================================================================

import 'lightgallery/dist/css/lightgallery.css';
import 'jquery-ui/themes/base/theme.css';
import 'jquery-ui/themes/base/datepicker.css';
import 'typeface-open-sans';
import './bootstrap.scss';
import './email_reaktion.scss';
import './fuer_einsteiger.scss';
import './index.scss';
import './konto_passwort.scss';
import './konto_strava.scss';
import './logs.scss';
import './profil.scss';
import './styles.scss';

export * from './components/index';
export * from './email_reaktion';
export * from './features/index';
export * from './fuer_einsteiger';
export * from './kontakt';
export * from './konto_passwort';
export * from './konto_strava';
export * from './logs';
export * from './profil';
export * from './scripts/index';
export * from './termine';

/* @ts-ignore: Ignore file is not a module. */
export * from 'bootstrap';
/* @ts-ignore: Ignore file is not a module. */
export * from 'lightgallery';
/* @ts-ignore: Ignore file is not a module. */
export * from 'lg-video';
/* @ts-expect-error: Ignore file is not a module. */
export * from 'jquery';
/* @ts-ignore: Ignore file is not a module. */
export * from 'jquery-ui/ui/widgets/datepicker';

// OLZ library (as generated by webpack, i.e. all exports of this file)
declare const olz: {[key: string]: any};

export function loaded(): void {
    // TODO: remove this!
    for (const key of Object.keys(olz)) {
        /* @ts-expect-error: Ignore type unsafety. */
        window[key] = olz[key];
    }
    /* @ts-expect-error: Ignore type unsafety. */
    // eslint-disable-next-line dot-notation
    window['$'] = $;

    $(() => {
        /* @ts-expect-error: lightGallery does actually exist! */
        $('.lightgallery').lightGallery({
            selector: 'a[data-src]',
        });
        $.datepicker.setDefaults({
            dateFormat: 'yy-mm-dd',
        });
        $('.datepicker').datepicker();
    });
    console.log('OLZ loaded!');
}
