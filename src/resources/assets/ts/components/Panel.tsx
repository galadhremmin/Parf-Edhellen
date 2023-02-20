import {
    IProps,
    PanelType,
} from './Panel._types';

const Panel: React.FunctionComponent<IProps> = (props: IProps) => {
    const {
        children,
        title,
        titleButton,
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
};

Panel.defaultProps = {
    title: null,
    type: PanelType.Info,
};

export default Panel;
