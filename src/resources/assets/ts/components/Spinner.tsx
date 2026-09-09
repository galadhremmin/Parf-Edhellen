import './Spinner.scss';

/**
 * A book lying open, turning its own pages.
 *
 * The outer element is the 3D stage; the inner one is the book itself, tilted
 * away from the viewer so it reads as lying on a surface rather than standing
 * upright facing us. Drawn in currentColor and sized in em, so it takes the
 * colour and scale of whatever it sits in.
 *
 * Purely decorative: every place that uses it pairs it with text saying what is
 * happening, so it is hidden from assistive technology.
 */
const Spinner = () => <span className="ed-book-spinner" aria-hidden="true">
    <span className="ed-book-spinner__book">
        <span className="ed-book-spinner__side ed-book-spinner__side--left"></span>
        <span className="ed-book-spinner__side ed-book-spinner__side--right"></span>
        <span className="ed-book-spinner__leaf"></span>
        <span className="ed-book-spinner__leaf"></span>
        <span className="ed-book-spinner__leaf"></span>
    </span>
</span>;

export default Spinner;
