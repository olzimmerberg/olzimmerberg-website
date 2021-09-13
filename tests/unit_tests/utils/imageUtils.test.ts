/* eslint-env jasmine */

import {loadImageFromBase64, getBase64FromCanvas, getResizedDimensions, getCanvasOfSize, getResizedCanvas} from '../../../src/utils/imageUtils';

const FAKE_IMAGE = new Image(800, 600);

describe('loadImageFromBase64', () => {
    it('creates a native image element', async () => {
        const promise = loadImageFromBase64(
            'data:image/gif;base64,R0lGODlhAQABAAAAACw=',
        );
        expect(promise).not.toEqual(undefined);
    });

    it('resolves on image load', async () => {
        const fakeImage = {} as HTMLImageElement;
        const fakeLoadEvent = {} as Event;
        const promise = loadImageFromBase64(
            'data:image/gif;base64,R0lGODlhAQABAAAAACw=',
            {image: fakeImage},
        );
        fakeImage.onload(fakeLoadEvent);
        const result = await promise;
        expect(result).toEqual(fakeImage);
    });

    it('rejects on image error', async () => {
        const fakeImage = {} as HTMLImageElement;
        const fakeErrorEvent = {} as Event;
        const promise = loadImageFromBase64(
            'data:image/gif;base64,R0lGODlhAQABAAAAACw=',
            {image: fakeImage},
        );
        fakeImage.onerror(fakeErrorEvent);
        try {
            await promise;
            fail('Error expected');
        } catch (err: unknown) {
            expect(err).not.toEqual(undefined);
        }
    });
});

describe('getResizedDimensions', () => {
    it('works when width > height and too big', () => {
        const result = getResizedDimensions(1024, 512, 800);
        expect(result).toEqual([800, 400]);
    });

    it('works when width < height and too big', () => {
        const result = getResizedDimensions(1024, 2048, 800);
        expect(result).toEqual([400, 800]);
    });

    it('works when width == height and too big', () => {
        const result = getResizedDimensions(1024, 1024, 800);
        expect(result).toEqual([800, 800]);
    });

    it('works when image is just not too big', () => {
        const result = getResizedDimensions(800, 600, 800);
        expect(result).toEqual([800, 600]);
    });

    it('works when image is not too big', () => {
        const result = getResizedDimensions(80, 120, 800);
        expect(result).toEqual([80, 120]);
    });
});

describe('getCanvasOfSize', () => {
    it('works', () => {
        const result = getCanvasOfSize(FAKE_IMAGE, 16, 16);
        expect(result instanceof HTMLCanvasElement).toEqual(true);
        expect(result.width).toEqual(16);
        expect(result.height).toEqual(16);
    });
});

describe('getResizedCanvas', () => {
    it('works with hack', () => {
        const result = getResizedCanvas(FAKE_IMAGE, 16);
        expect(result instanceof HTMLCanvasElement).toEqual(true);
        expect(result.width).toEqual(16);
        expect(result.height).toEqual(12);
    });

    it('works without hack', () => {
        const result = getResizedCanvas(FAKE_IMAGE, 400);
        expect(result instanceof HTMLCanvasElement).toEqual(true);
        expect(result.width).toEqual(400);
        expect(result.height).toEqual(300);
    });
});

describe('getBase64FromCanvas', () => {
    it('works with explicit image/jpeg', () => {
        const fakeCanvas = {toDataURL: () => 'fake-data-url'} as HTMLCanvasElement;
        const result = getBase64FromCanvas(fakeCanvas);
        expect(result).toEqual('fake-data-url');
    });

    it('works with implicit image/jpeg', () => {
        const fakeCanvas = {
            toDataURL: (...args) => {
                if (args.length > 0) {
                    throw new Error('test');
                }
                return 'fake-data-url';
            },
        } as HTMLCanvasElement;
        const result = getBase64FromCanvas(fakeCanvas);
        expect(result).toEqual('fake-data-url');
    });
});
