import { useLanguageLookup } from './LanguageLookupContext';
import ReferenceLink from './ReferenceLink';
import type { IProps } from './RootForm._types';

import './RootForm.scss';

/**
 * Renders a root-speech ancestor the way Eldamo typesets roots: "√" + UPPERCASE form, with the
 * language's own `mark` (e.g. "M" for Middle Primitive Elvish) as a superscript prefix — Eldamo
 * writes this as "ᴹ√GALAD". `mark` is configured per language (see the `languages.mark` column;
 * null by default), so no period/id list needs hardcoding here. Roots are cited in their own
 * uppercase spelling (`parentForm`) — unlike ordinary ancestors, they should never fall back to
 * a resolved entry's plain-cased `Word` record (e.g. "gal"), since that loses the root
 * convention entirely.
 */
const RootForm = (props: IProps) => {
    const { form, languageId, lexicalEntryId, url } = props;
    const { getLanguage } = useLanguageLookup();
    const mark = languageId ? getLanguage(languageId)?.mark : null;

    return <ReferenceLink className="RootForm" lexicalEntryId={lexicalEntryId} url={url}>
        {mark && <sup className="RootForm--mark">{mark}</sup>}
        <span className="RootForm--radical">√</span>
        {form.toLocaleUpperCase()}
    </ReferenceLink>;
};

export default RootForm;
