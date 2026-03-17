enum Actions {
    InitializePuzzle = 'ED_CROSSWORD_INITIALIZE',
    LoadDraft        = 'ED_CROSSWORD_DRAFT_LOAD',
    SelectCell       = 'ED_CROSSWORD_CELL_SELECT',
    EnterLetter      = 'ED_CROSSWORD_LETTER_ENTER',
    DeleteLetter     = 'ED_CROSSWORD_LETTER_DELETE',
    CheckResult      = 'ED_CROSSWORD_CHECK_RESULT',
    ClearCheck       = 'ED_CROSSWORD_CHECK_CLEAR',
    UseCheck         = 'ED_CROSSWORD_CHECK_USE',
    UseReveal        = 'ED_CROSSWORD_REVEAL_USE',
    RevealCells      = 'ED_CROSSWORD_REVEAL_CELLS',
    SetIsAssisted    = 'ED_CROSSWORD_ASSISTED_SET',
    SetStage         = 'ED_CROSSWORD_STAGE_SET',
    SetTime          = 'ED_CROSSWORD_TIME_SET',
    ResumeTimer      = 'ED_CROSSWORD_TIMER_RESUME',
    CompletionResult = 'ED_CROSSWORD_COMPLETION_RESULT',
}

export default Actions;
