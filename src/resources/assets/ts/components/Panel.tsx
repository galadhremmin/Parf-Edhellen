import {
    IProps
} from './Panel._types';

function Panel(props: IProps) {
    const {
        children,
        title = null,
        titleButton,
//      type = PanelType.Info, not supported
    } = props;

    return <div className="card mb-3">
        <div className="card-body">
            {!! title && <h3 className="panel-title">
                {title}
                {titleButton && <span className="float-end">{titleButton}</span>}
            </h3>}
            {children}
        </div>
    </div>;
}

export default Panel;
