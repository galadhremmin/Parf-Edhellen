import React from 'react';

import {
    IProps,
    PanelType,
} from './Panel._types';

const Panel: React.SFC<IProps> = (props: IProps) => {
    const {
        children,
        title,
        type,
    } = props;

    return <div className={`panel panel-${type}`}>
        {title && <div className="panel-heading">
            <h3 className="panel-title">{title}</h3>
        </div>}
        <div className="panel-body">
            {children}
        </div>
    </div>;
};

Panel.defaultProps = {
    title: null,
    type: PanelType.Info,
};

export default Panel;
