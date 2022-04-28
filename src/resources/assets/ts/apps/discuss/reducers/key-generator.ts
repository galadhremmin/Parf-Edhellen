import { DEFAULT_COLLECTIVIZE_KEY } from '@root/utilities/redux/collectivize';

export const keyGenerator = (entityType: string, entityId: number) => (entityType && entityId) ? `${entityType}|${entityId.toString(10)}` : DEFAULT_COLLECTIVIZE_KEY;
