import { red, green, orange } from '@material-ui/core/colors';
import { createMuiTheme } from '@material-ui/core/styles';

// A custom theme for this app
const theme = createMuiTheme({
  palette: {
    primary: {
      main: '#556cd6',
    },
    secondary: {
      main: '#19857b',
    },
    error: {
      main: '#DC143C',//red.A400,
    },
    success: {
		main: green.A200,
    },
    background: {
      default: '#fff',
	},
	status: {
		danger: '#DC143C',//orange[500]
	}
  },
});

export default theme;
