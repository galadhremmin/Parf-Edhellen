import React, { useEffect, useState } from 'react';

import HtmlInject from '@root/components/HtmlInject';
import GlossDetail from './GlossDetail';
import { IProps } from './GlossDetails._types';

import './GlossDetails.scss';

const GlossDetails = (props: IProps) => {
    const {
        gloss,
        onReferenceLinkClick,
        showDetails,
    } = props;
    return <>
        <HtmlInject html={gloss.comments} onReferenceLinkClick={onReferenceLinkClick} />
        {showDetails && gloss.glossDetails.map(
            (d) => <GlossDetail key={`${d.order}_${d.category}`} detail={d} onReferenceLinkClick={onReferenceLinkClick} />,
        )}
    </>;
};

GlossDetails.defaultProps = {
    showDetails: true,
};

export default GlossDetails;
