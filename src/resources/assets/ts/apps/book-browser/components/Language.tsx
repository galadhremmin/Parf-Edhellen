import type { IProps } from './Language._types';

import './Language.scss';

function Language(props: IProps) {
    const {
        language,
    } = props;

    return <>
        <h2 className="Language__header">
            <span className="language-name">{ language.name }</span>
            {!!language.tengwar && <>&nbsp;<span className="tengwar">{ language.tengwar }</span></>}
            {!!language.isUnusual && <span className="language-unusual">&nbsp;†</span>}
        </h2>
        {language.category && <h3 className="Language__subheader">{language.category}</h3>}
    </>;
}

export default Language;
