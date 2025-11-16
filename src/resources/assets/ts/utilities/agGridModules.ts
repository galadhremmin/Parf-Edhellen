import { ModuleRegistry } from '@ag-grid-community/core';
import { ClientSideRowModelModule } from '@ag-grid-community/client-side-row-model';
import { InfiniteRowModelModule } from '@ag-grid-community/infinite-row-model';

let hasRegistered = false;

function registerModules() {
    if (hasRegistered) {
        return;
    }

    ModuleRegistry.registerModules([
        ClientSideRowModelModule,
        InfiniteRowModelModule,
    ]);

    hasRegistered = true;
}

registerModules();

export const ensureAgGridModules = registerModules;

