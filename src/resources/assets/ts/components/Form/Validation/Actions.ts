export enum Actions {
    SetValidationErrors = 'ED_VALIDATION_ERRORS_SET',
}

export const setValidationErrors = (e: any) => ({
    errors: e,
    type: Actions.SetValidationErrors,
});
