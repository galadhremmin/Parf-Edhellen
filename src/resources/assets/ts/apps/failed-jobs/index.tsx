import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import FailedJobsList from '../system-log/components/FailedJobsList';
import registerApp from '../app';

export default registerApp(withPropInjection(FailedJobsList, {
    logApi: DI.LogApi,
}));
