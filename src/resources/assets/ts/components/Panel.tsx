import classNames from 'classnames';
import {
    IProps
} from './Panel._types';

function Panel(props: IProps) {
    const {
        children,
        title = null,
        titleButton,
        shadow,
//      type = PanelType.Info, not supported
    } = props;

    return <div className={classNames("card", "mb-3", {"shadow-lg": shadow})}>
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
