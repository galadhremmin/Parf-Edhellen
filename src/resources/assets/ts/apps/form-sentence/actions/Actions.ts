enum Actions {
    ReceiveSentence       = 'ED_FORM_SENTENCE_LOAD',
    ReceiveFragment       = 'ED_FORM_SENTENCE_FRAGMENT_LOAD',
    ReceiveTranslation    = 'ED_FORM_SENTENCE_TRANSLATION_LOAD',
    ReceiveTransformation = 'ED_FORM_SENTENCE_TRANSFORMATION_LOAD',
    SetFragment           = 'ED_FORM_SENTENCE_FRAGMENT_SET',
    SetFragmentField      = 'ED_FORM_SENTENCE_FRAGMENT_FIELD_SET',
    SetTranslation        = 'ED_FORM_SENTENCE_TRANSLATION_SET',
    SetField              = 'ED_FORM_SENTENCE_FIELD_SET',
    SetLatinText          = 'ED_FORM_SENTENCE_TEXT_LATIN_SET',
    SetTransformation     = 'ED_FORM_SENTENCE_TRANSFORMATION_SET',
}

export default Actions;
