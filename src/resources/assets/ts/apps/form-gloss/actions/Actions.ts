enum Actions {
    ReceiveGloss = 'ED_FORM_GLOSS_LOAD',
    ReceiveInflections = 'ED_FORM_INFLECTIONS_LOAD',
    SetGlossField = 'ED_FORM_GLOSS_FIELD_SET',
    SetInflectionGroup = 'ED_FORM_INFLECTION_GROUP_SET',
    UnsetInflectionGroup = 'ED_FORM_INFLECTION_GROUP_UNSET',
    CreateBlankInflectionGroup = 'ED_FORM_INFLECTION_GROUP_CREATE',
}

export default Actions;
