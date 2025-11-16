import classNames from '@root/utilities/ClassNames';
import type { IProps } from './Panel._types';

function Panel(props: IProps) {
    const {
        children,
        className,
        title = null,
        titleButton,
        shadow,
//      type = PanelType.Info, not supported
    } = props;

    return <div className={classNames("card", "mb-3", {"shadow": shadow}, className ?? '')}>
        <div className="card-body">
            {!! title && <h3 className="panel-title">
                {title}
                {titleButton && <span className="float-end">{titleButton}</span>}
            </h3>}
            {children ?? ''}
        </div>
    </div>;
}

export default Panel;
