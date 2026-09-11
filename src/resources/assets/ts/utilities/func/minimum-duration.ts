/**
 * The shortest time a loading state stays on screen, in milliseconds.
 *
 * Without a floor, the glossary's loading state lasts exactly as long as the
 * network takes: a flicker on a fast connection, a long wait on a slow one, and
 * a different-feeling interaction every time. Holding it for a beat makes every
 * expansion feel like the same deliberate transition.
 *
 * Kept short on purpose. This is long enough to register as a transition rather
 * than a flash, and short enough that nobody would describe it as waiting.
 */
export const MinimumLoadingDuration = 350;

/**
 * Resolves `work`, but never sooner than `duration` milliseconds.
 *
 * The delay runs alongside the work rather than after it, so a request that
 * takes longer than the floor is not slowed down at all -- only the ones that
 * would otherwise finish too quickly to see.
 *
 * Rejections are not held back: an error should surface as soon as it happens.
 */
export default async function withMinimumDuration<T>(
    work: Promise<T>,
    duration: number = MinimumLoadingDuration,
): Promise<T> {
    const [ result ] = await Promise.all([
        work,
        new Promise<void>((resolve) => setTimeout(resolve, duration)),
    ]);

    return result;
}
