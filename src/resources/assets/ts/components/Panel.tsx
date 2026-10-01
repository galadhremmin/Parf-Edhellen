import classNames from '@root/utilities/ClassNames';
import type { IProps } from './Panel._types';

/**
 * A leaf of the book: a Bootstrap card, styled site-wide by `_scss/_panel.scss`.
 */
function Panel(props: IProps) {
    const {
        children,
        className,
        eyebrow,
        headingLevel = 3,
        title = null,
        titleButton,
//      type = PanelType.Info, not supported
    } = props;

    const Heading = headingLevel === 2 ? 'h2' : 'h3';

    return <section className={classNames('card', 'mb-4', className ?? '')}>
        <div className="card-body">
            {!! eyebrow && <p className="ed-label mb-1">{eyebrow}</p>}
            {!! title && <Heading className="panel-title">
                {title}
                {titleButton && <span className="float-end">{titleButton}</span>}
            </Heading>}
            {children ?? ''}
        </div>
    </section>;
}

export default Panel;
