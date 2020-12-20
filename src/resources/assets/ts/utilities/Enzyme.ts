import { configure } from 'enzyme';
import ReactSixteenAdapter from '@wojtekmaj/enzyme-adapter-react-17';

// Configures Enzyme for React 16.x
// See: https://github.com/airbnb/enzyme/blob/3fb45940f44ef73fcd6bb370103af5f4101d2051/README.md
configure({ adapter: new ReactSixteenAdapter() });

// React 16.x depends on `requestAnimationFrame` but it does not exist by default on Node.
window.requestAnimationFrame = window.requestAnimationFrame ||
    ((callback: FrameRequestCallback) => setTimeout(callback, 0) as any);
