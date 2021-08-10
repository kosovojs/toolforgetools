import 'bootstrap/dist/css/bootstrap.min.css';
import React, { Component } from 'react';
import { BrowserRouter as Router, Link, Route, Switch } from 'react-router-dom'
import Header from './Header';
import Table from './Jury';
import New from './New';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const Dashboard = () => (
  <div className="container">
    <h2>Welcome</h2>
    Welcome to EST LAT article writing contest to celebrate 100th birthday of both countries!
  </div>
)

const NotFound = ({ location }) => (
  <div>
    <h3>Did not found page for <code>{location.pathname}</code></h3>
  </div>
)

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoggedIn: false,
      userName: null
    }
  }
  
  componentDidMount() {
    fetch("http://localhost/estlat/src/components/api.php?act=userinfo")
        .then(res => res.json())
        .then(
          (result) => {
            if (!('error' in result)){
              {this.setState({isLoggedIn: true,userName:result['query']['userinfo']['name']})}
          }
        },
          (error) => {
            //set state
          }
        )
  }

  render() {
    return (
      <Router>
        <div>
          <Header isLogged={this.state.isLoggedIn} userName={this.state.userName} />
          <Switch>
            <Route exact path="/" component={Dashboard} />
            <Route exact path="/jury" component={Table} />
            <Route exact path="/new" component={New} />{/*()=><New />*/}
            <Route component={NotFound} />
          </Switch>
          <ToastContainer
          position="bottom-right"
          autoClose={1000}
          hideProgressBar={false}
          newestOnTop={false}
          closeOnClick
          rtl={false}
          pauseOnVisibilityChange
          draggable={false}
          pauseOnHover
          />
        </div>
      </Router>
    );
  }
}

export default App;
