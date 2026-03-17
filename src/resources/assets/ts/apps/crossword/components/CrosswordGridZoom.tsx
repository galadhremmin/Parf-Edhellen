import { useEffect, useRef, type CSSProperties, type ReactNode } from 'react';
import './CrosswordGridZoom.scss';

interface IProps {
    cols: number;
    children: ReactNode;
    activeRow?: number | null;
    activeCol?: number | null;
}

/** Minimum touch-target size (Apple HIG / Material Design recommendation). */
const MIN_CELL_PX = 44;
const MIN_SCALE = 1;
const MAX_SCALE = 5;
/** Pixels of movement before a drag gesture is recognised (not a tap/click). */
const DRAG_THRESHOLD = 4;

/**
 * Wraps the crossword grid with zoom and pan support for both touch and mouse.
 *
 * Touch : one-finger tap selects a cell; two-finger drag pans; two-finger pinch zooms.
 * Mouse : wheel to zoom (cursor position stays fixed); drag to pan.
 *         A short click (movement < DRAG_THRESHOLD) still fires as a cell click.
 */
export default function CrosswordGridZoom({ cols, children, activeRow, activeCol }: IProps) {
    const outerRef = useRef<HTMLDivElement>(null);
    const innerRef = useRef<HTMLDivElement>(null);

    // Filled in by the main effect so the pan-to-active effect can call into
    // the closure that owns tx/ty/scale/clamp/applyTransform.
    const panToActiveRef = useRef<(() => void) | null>(null);

    useEffect(() => {
        const outer = outerRef.current;
        const inner = innerRef.current;
        if (!outer || !inner) return;

        let scale = MIN_SCALE;
        let tx = 0;
        let ty = 0;

        // ── Shared helpers ──────────────────────────────────────────────────

        function applyTransform() {
            inner.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
        }

        function clamp() {
            const ow = outer.clientWidth;
            const oh = outer.clientHeight;
            const iw = inner.offsetWidth;
            const ih = inner.offsetHeight;
            tx = Math.max(Math.min(0, ow - iw * scale), Math.min(0, tx));
            ty = Math.max(Math.min(0, oh - ih * scale), Math.min(0, ty));
        }

        function zoomAt(originX: number, originY: number, newScale: number) {
            newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, newScale));
            tx = originX - (originX - tx) * (newScale / scale);
            ty = originY - (originY - ty) * (newScale / scale);
            scale = newScale;
            clamp();
            applyTransform();
            updateCursor();
        }

        function resetIfAtMinScale() {
            if (scale <= 1.05) {
                scale = MIN_SCALE;
                clamp();
                applyTransform();
                updateCursor();
            }
        }

        function updateCursor() {
            outer.style.cursor = scale > 1 ? 'grab' : '';
        }

        // ── Touch: one-finger tap to select + two-finger pinch/pan ─────────

        let prevA = { x: 0, y: 0 };
        let prevB = { x: 0, y: 0 };
        let pinchTracking  = false;
        let touchDragged   = false;
        let touchDragStart = { x: 0, y: 0 };

        const onTouchStart = (e: TouchEvent) => {
            if (e.touches.length === 2) {
                pinchTracking  = true;
                touchDragged   = false;
                touchDragStart = { x: (e.touches[0].clientX + e.touches[1].clientX) / 2, y: (e.touches[0].clientY + e.touches[1].clientY) / 2 };
                prevA = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                prevB = { x: e.touches[1].clientX, y: e.touches[1].clientY };
            } else if (e.touches.length === 1) {
                pinchTracking = false;
                touchDragged  = false;
            }
        };

        const onTouchMove = (e: TouchEvent) => {
            if (e.touches.length === 2 && pinchTracking) {
                e.preventDefault();

                const curA = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                const curB = { x: e.touches[1].clientX, y: e.touches[1].clientY };

                const prevDist = Math.hypot(prevB.x - prevA.x, prevB.y - prevA.y);
                const curDist  = Math.hypot(curB.x  - curA.x,  curB.y  - curA.y);
                const ratio    = prevDist > 0 ? curDist / prevDist : 1;

                const prevMid = { x: (prevA.x + prevB.x) / 2, y: (prevA.y + prevB.y) / 2 };
                const curMid  = { x: (curA.x  + curB.x)  / 2, y: (curA.y  + curB.y)  / 2 };

                const rect = outer.getBoundingClientRect();
                zoomAt(curMid.x - rect.left, curMid.y - rect.top, scale * ratio);

                // Two-finger pan: track midpoint movement
                tx += curMid.x - prevMid.x;
                ty += curMid.y - prevMid.y;
                clamp();
                applyTransform();

                touchDragged = Math.hypot(curMid.x - touchDragStart.x, curMid.y - touchDragStart.y) > DRAG_THRESHOLD;

                prevA = curA;
                prevB = curB;
            }
        };

        const onTouchEnd = (e: TouchEvent) => {
            if (e.touches.length < 2) {
                pinchTracking = false;
                resetIfAtMinScale();
            } else {
                prevA = { x: e.touches[0].clientX, y: e.touches[0].clientY };
                prevB = { x: e.touches[1].clientX, y: e.touches[1].clientY };
            }
        };

        // ── Mouse drag → pan ─────────────────────────────────────────────────

        let mouseDown = false;
        let dragged   = false;
        let lastMouse = { x: 0, y: 0 };
        let dragStart = { x: 0, y: 0 };

        const onMouseDown = (e: MouseEvent) => {
            if (e.button !== 0) return;
            mouseDown = true;
            dragged   = false;
            dragStart = { x: e.clientX, y: e.clientY };
            lastMouse = { x: e.clientX, y: e.clientY };
        };

        const onMouseMove = (e: MouseEvent) => {
            if (!mouseDown) return;
            if (!dragged && Math.hypot(e.clientX - dragStart.x, e.clientY - dragStart.y) > DRAG_THRESHOLD) {
                dragged = true;
                outer.style.cursor = 'grabbing';
            }
            if (dragged) {
                tx += e.clientX - lastMouse.x;
                ty += e.clientY - lastMouse.y;
                clamp();
                applyTransform();
            }
            lastMouse = { x: e.clientX, y: e.clientY };
        };

        const onMouseUp = () => {
            mouseDown = false;
            updateCursor();
        };

        // Swallow the click that follows a drag so the cell under the release
        // point is not accidentally selected.
        const onClickCapture = (e: MouseEvent) => {
            if (dragged || touchDragged) {
                e.stopPropagation();
                dragged      = false;
                touchDragged = false;
            }
        };

        // ── Register / cleanup ───────────────────────────────────────────────

        outer.addEventListener('touchstart',  onTouchStart,   { passive: true });
        outer.addEventListener('touchmove',   onTouchMove,    { passive: false });
        outer.addEventListener('touchend',    onTouchEnd,     { passive: true });
        outer.addEventListener('touchcancel', onTouchEnd,     { passive: true });
        outer.addEventListener('mousedown',   onMouseDown);
        outer.addEventListener('mousemove',   onMouseMove);
        outer.addEventListener('mouseup',     onMouseUp);
        outer.addEventListener('mouseleave',  onMouseUp);
        outer.addEventListener('click',       onClickCapture, { capture: true });

        // ── Pan to active cell ───────────────────────────────────────────────
        // Called from the separate activeRow/activeCol effect below.
        // Lives inside this closure so it can read and mutate tx/ty/scale.
        panToActiveRef.current = () => {
            const cell = inner.querySelector('.CrosswordGrid__cell--active');
            if (!cell) return;

            const outerRect = outer.getBoundingClientRect();
            const cellRect  = cell.getBoundingClientRect();

            const fadeMargin = 24; // matches $cw-zoom-fade (~1.5rem)
            const visible =
                cellRect.left   >= outerRect.left   + fadeMargin &&
                cellRect.right  <= outerRect.right  - fadeMargin &&
                cellRect.top    >= outerRect.top    + fadeMargin &&
                cellRect.bottom <= outerRect.bottom - fadeMargin;
            if (visible) return;

            // Pan horizontally to centre; vertically to near the top so the
            // cell stays above the virtual keyboard in landscape mode.
            const cellCx = cellRect.left - outerRect.left + cellRect.width  / 2;
            const cellTop = cellRect.top - outerRect.top;
            tx += outerRect.width / 2 - cellCx;
            ty += fadeMargin - cellTop;
            clamp();
            applyTransform();
        };

        return () => {
            panToActiveRef.current = null;
            outer.removeEventListener('touchstart',  onTouchStart);
            outer.removeEventListener('touchmove',   onTouchMove);
            outer.removeEventListener('touchend',    onTouchEnd);
            outer.removeEventListener('touchcancel', onTouchEnd);
            outer.removeEventListener('mousedown',   onMouseDown);
            outer.removeEventListener('mousemove',   onMouseMove);
            outer.removeEventListener('mouseup',     onMouseUp);
            outer.removeEventListener('mouseleave',  onMouseUp);
            outer.removeEventListener('click',       onClickCapture, { capture: true } as EventListenerOptions);
        };
    }, []);

    // When the active cell changes, pan to it after the DOM has been committed.
    useEffect(() => {
        if (activeRow == null || activeCol == null) return;
        const rafId = requestAnimationFrame(() => panToActiveRef.current?.());
        return () => cancelAnimationFrame(rafId);
    }, [activeRow, activeCol]);

    return (
        <>
            <p className="CrosswordGridZoom__hint" aria-hidden="true">
                Tap a cell to select it. Use two fingers to pan and pinch to zoom.
            </p>
            <div ref={outerRef} className="CrosswordGridZoom">
                <div
                    ref={innerRef}
                    className="CrosswordGridZoom__inner"
                    style={{ '--cw-zoom-cols': cols, '--cw-min-cell': `${MIN_CELL_PX}px` } as CSSProperties}
                >
                    {children}
                </div>
            </div>
        </>
    );
}
