type RgbColor = [number, number, number];
type ElementType = 'background'|'border';
type Timeout = ReturnType<typeof setTimeout>;

interface ElementState {
    timer?: Timeout;
    step?: number;
    r?: number;
    g?: number;
    b?: number;
}

const elementsState: {[id: string]: ElementState} = {};

// main function to process the fade request //
export function colorFade(
    id: string,
    element: ElementType,
    start: string,
    end: string,
    stepsArg: number,
    speedArg: number,
): void {
    const state = elementsState[id] || {};
    const steps = stepsArg || 20;
    const speed = speedArg || 20;
    clearInterval(state.timer);
    const endrgb = colorConv(end);
    const er = endrgb[0];
    const eg = endrgb[1];
    const eb = endrgb[2];
    if (!state.r) {
        const startrgb = colorConv(start);
        const r = startrgb[0];
        const g = startrgb[1];
        const b = startrgb[2];
        state.r = r;
        state.g = g;
        state.b = b;
    }
    let rint = Math.round(Math.abs(state.r - er) / steps);
    let gint = Math.round(Math.abs(state.g - eg) / steps);
    let bint = Math.round(Math.abs(state.b - eb) / steps);
    if (rint === 0) { rint = 1; }
    if (gint === 0) { gint = 1; }
    if (bint === 0) { bint = 1; }
    state.step = 1;
    state.timer = setInterval(() => { animateColor(id, element, steps, er, eg, eb, rint, gint, bint); }, speed);
    elementsState[id] = state;
}

// incrementally close the gap between the two colors //
function animateColor(
    id: string,
    element: ElementType,
    steps: number,
    er: number, eg: number, eb: number,
    rint: number, gint: number, bint: number,
): void {
    const state = elementsState[id] || {};
    const target = document.getElementById(id);
    let color;
    if (state.step <= steps) {
        let r = state.r;
        let g = state.g;
        let b = state.b;
        if (r >= er) {
            r = r - rint;
        } else {
            r = r + rint;
        }
        if (g >= eg) {
            g = g - gint;
        } else {
            g = g + gint;
        }
        if (b >= eb) {
            b = b - bint;
        } else {
            b = b + bint;
        }
        color = `rgb(${r},${g},${b})`;
        if (element === 'background') {
            target.style.backgroundColor = color;
        } else if (element === 'border') {
            target.style.borderColor = color;
        } else {
            target.style.color = color;
        }
        state.r = r;
        state.g = g;
        state.b = b;
        state.step = state.step + 1;
    } else {
        clearInterval(state.timer);
        color = `rgb(${er},${eg},${eb})`;
        if (element === 'background') {
            target.style.backgroundColor = color;
        } else if (element === 'border') {
            target.style.borderColor = color;
        } else {
            target.style.color = color;
        }
    }
    elementsState[id] = state;
}

// convert the color to rgb from hex //
function colorConv(color: string): RgbColor {
    return [parseInt(color.substring(0, 2), 16),
        parseInt(color.substring(2, 4), 16),
        parseInt(color.substring(4, 6), 16)];
}

/* @ts-expect-error: Ignore type unsafety. */
// eslint-disable-next-line dot-notation
window['colorFade'] = colorFade;
