/**
 * idle → dealing → answering{i} ⇄ flipped{i} → submitting → summary,
 * plus summary → dealing for _retry missed words_.
 * `empty` and `failed` are the two dead ends of `dealing`.
 */
export type DeckPhase =
  | 'idle'
  | 'dealing'
  | 'answering'
  | 'flipped'
  | 'submitting'
  | 'summary'
  | 'empty'
  | 'failed';
