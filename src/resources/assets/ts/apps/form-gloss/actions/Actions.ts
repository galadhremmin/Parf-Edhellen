enum Actions {
    ReceiveLexicalEntry = 'ED_FORM_LEXICAL_ENTRY_LOAD',
    ReceiveInflections = 'ED_FORM_INFLECTIONS_LOAD',
    SetLexicalEntryField = 'ED_FORM_LEXICAL_ENTRY_FIELD_SET',
    SetInflectionGroup = 'ED_FORM_INFLECTION_GROUP_SET',
    UnsetInflectionGroup = 'ED_FORM_INFLECTION_GROUP_UNSET',
    CreateBlankInflectionGroup = 'ED_FORM_INFLECTION_GROUP_CREATE',
}

export default Actions;
