//import 'bootstrap/dist/css/bootstrap.min.css';
import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import {Navbar, Nav, NavItem} from 'react-bootstrap';
import {LinkContainer, IndexLinkContainer} from 'react-router-bootstrap';

class NavLink extends Component {

  render() {
      return (
        <li className={"nav-item " + (this.props.isActive ? "active": "")}>
                  <Link 
                    className="nav-link" 
                    to={this.props.path}
                    onClick={() => this.props.onClick()}
                  >
              {this.props.text}</Link>
        </li>
      );
  }
}

class UserInfo extends Component {
  render() {
    const {islogged, name} = this.props;

    if (islogged) {
      return <span>Hi, {name}!</span>
    } else {
      return null
    }
  }
}

class Login extends Component {
  render() {
    const {islogged} = this.props;

    if (islogged) {
      return <NavItem href="https://tools.wmflabs.org/edgars/index.php?action=logout"><a target="_parent">Logout</a></NavItem>
    } else {
      return <NavItem href="https://tools.wmflabs.org/edgars/index.php?action=authorize"><a target="_parent">Login</a></NavItem>
    }
  }
}

class Header extends Component {
  render() {
    const {isLogged, userName} = this.props;

    return (
      <Navbar>
            <Nav>
              <IndexLinkContainer to="/">
                <NavItem>EST-LAT 100</NavItem>
              </IndexLinkContainer>
              <LinkContainer to="/jury"><NavItem>Jury</NavItem></LinkContainer>
              <LinkContainer to="/new"><NavItem>New article</NavItem></LinkContainer>
              {/*<LinkContainer to="/lists"><NavItem>Article lists</NavItem></LinkContainer>*/}
            </Nav>
            <Nav pullRight>
              <NavItem><UserInfo islogged={isLogged} name={userName} /></NavItem>
              <Login islogged={isLogged} />
            </Nav>
      </Navbar>
    );
  }
}

export default Header;