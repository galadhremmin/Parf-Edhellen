enum Actions {
    ReceiveSentence    = 'ED_FORM_SENTENCE_LOAD',
    ReceiveFragment    = 'ED_FORM_SENTENCE_FRAGMENT_LOAD',
    ReceiveTranslation = 'ED_FORM_SENTENCE_TRANSLATION_LOAD',
    SetFragment        = 'ED_FORM_SENTENCE_FRAGMENT_SET',
    SetTranslation     = 'ED_FORM_SENTENCE_TRANSLATION_SET',
    SetField           = 'ED_FORM_SENTENCE_FIELD_SET',
}

export default Actions;
