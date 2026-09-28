import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import registerApp from '../app';
import SenseReviewer from './components/SenseReviewer';

export default registerApp(withPropInjection(SenseReviewer, {
    api: DI.SenseReviewApi,
    senseApi: DI.SenseApi,
}));
