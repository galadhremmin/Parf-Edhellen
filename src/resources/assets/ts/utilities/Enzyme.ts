import { configure } from 'enzyme';
import ReactSixteenAdapter from 'enzyme-adapter-react-16';

// Configures Enzyme for React 16.x
// See: https://github.com/airbnb/enzyme/blob/3fb45940f44ef73fcd6bb370103af5f4101d2051/README.md
configure({ adapter: new ReactSixteenAdapter() });
