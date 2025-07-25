import GlossGroupSelect from '@root/components/Form/GlossGroupSelect';
import SpeechSelect from '@root/components/Form/SpeechSelect';

import { IProps } from './AdditionalSearchParameters._types';

import './AdditionalSearchParameters.scss';

function AdditionalSearchParameters(props: IProps) {
    const {
        lexicalEntryGroupId = 0,
        onLexicalEntryGroupIdChange,
        onSpeechIdChange,
        speechId = 0,
    } = props;

    return <div className="AdditionalSearchParameters">
        <div>
            <SpeechSelect
                allowEmpty={true}
                emptyText="Any parts of speech"
                name="speech"
                onChange={onSpeechIdChange}
                value={speechId}
            />
        </div>
        <div>
            <GlossGroupSelect
                allowEmpty={true}
                emptyText="Any group"
                name="lexical-entry-group"
                onChange={onLexicalEntryGroupIdChange}
                value={lexicalEntryGroupId}
            />
        </div>
    </div>;
}

export default AdditionalSearchParameters;
