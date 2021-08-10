//import 'bootstrap/dist/css/bootstrap.min.css';
import React, { Component } from 'react';
import { ToastContainer, toast } from 'react-toastify';
//import Toggle from 'react-bootstrap-toggle';
import NumericInput from 'react-numeric-input';
import {AsyncTypeahead} from 'react-bootstrap-typeahead';
import 'react-bootstrap-typeahead/css/Typeahead.css';
import Toggle from 'react-toggle'
import "react-toggle/style.css";
//import makeAndHandleRequest from './makeAndHandleRequest';
import $ from 'jquery';

//https://stackoverflow.com/questions/41296668/reactjs-form-input-validation

class PointsSum extends Component {
  render() {
    const { wd, news, imgs, size } = this.props;
    var sum = [wd, news, imgs].reduce(function(count, item) {
      return count + (item === false ? 0 : 1);
    }, 0);

    return <div>You get {sum+size} points for this article</div>
  }
}

//http://aaronshaf.github.io/react-toggle/
//https://www.npmjs.com/package/react-switch
class New extends Component {
  constructor(props) {
    super(props);
    this.state = {
      wikipedia: "et",
      articleName: null,
      userName: null,
      newArticle: false,
      imageCount: false,
      wikidataCount: false,
      articleSize: 0,
      optionsAsync: [],
      isUserLoading: false,
      isArticleLoading: false,
      hasUserBennLoggedIn: false
    };
    this.onToggle = this.onToggle.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.saveToDB = this.saveToDB.bind(this);
    
    this.handleSizeChange = this.handleSizeChange.bind(this);
    
    //https://stackoverflow.com/questions/41296668/reactjs-form-input-validation
    this.handleNewChange = this.handleChange.bind(this, 'newArticle');//onChange={this.handleChange.bind(this, "address")}
    //onChange={(event) => this.handleUserInput(event)}
    this.handleImageChange = this.handleChange.bind(this, 'imageCount');
    this.handleWikidataChange = this.handleChange.bind(this, 'wikidataCount');
    
  }
  /*
  componentDidMount() {
    console.log(this.props.isLogged);
    if (!this.props.isLogged) {
      //toast.error("You need to log in to save articles!", { autoClose: 5000 });
    }
  }
  */
  /*
  ar onChange={(event) => this.handleUserInput(event)}:

  handleUserInput (e) {
  const name = e.target.name;
  const value = e.target.value;
  this.setState({[name]: value});
}
*/
  
  handleChange (key, event) {
    //console.log(key);
    //console.log(event.target.checked);
    this.setState({ [key]: event.target.checked });
    //console.log(this.state[key]);
  }

  saveToDB(e) {
    e.preventDefault();
    console.log(this.state);
    if (this.state.articleName === null || this.state.userName === null) {
      toast.error("You have to fill both article and user field!", { autoClose: false });
    } else {
      console.log('valid');
      const toSend = {
        'wp':this.state.wikipedia,
        'user':this.state.userName,
        'article':this.state.articleName,
        'size':this.state.articleSize,
        'new':this.state.newArticle,
        'images':this.state.imageCount,
        'wikidata':this.state.wikidataCount
      };
      
      $.ajax({
        type: "post",
        url: "http://localhost/estlat/src/components/api.php",
        dataType:"json",
        data: {'data':toSend,'act':'new_article'},
      })
	    .done(function (response) {
			  if(response.status === "success") {
          toast.success("Article succesfully saved!", { autoClose: 3500 });
        } else if(response.status === "error") {
          toast.error("Error: " +response.message, { autoClose: false });
        }
      })
	    .fail(function (XMLHttpRequest, textStatus, errorThrown) {
			  console.log("Status: " + textStatus);
			  console.log("Error: " + errorThrown);
        console.log(XMLHttpRequest);
        toast.error("Error, see console for original error", { autoClose: false });
      });
    }
    
    //{this.state.emailError ? <span style={{color: "red"}}>Please Enter valid email address</span> : ''}
  }

  handleSizeChange (event) {
    //console.log(key);
    //console.log(event.target.checked);
    //console.log(event);
    this.setState({ articleSize: event });
    //console.log(this.state[key]);
  }

  onToggle(event) {
    //const target = event.target;
    //const name = target.name;
    this.setState({ toggleActive: !this.state.toggleActive });
  }
  
  //https://codepen.io/gaearon/pen/wgedvV?editors=0010
  //https://stackoverflow.com/questions/43959116/using-a-single-handleinputchange-method-to-for-multiple-input-fields-react
  handleInput(event) {
    const target = event.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;
    //console.log(target);
    //console.log(value);
    //console.log(name);

    this.setState({
      [name]: value
    });
  }
  _handleUserSearch = (query) => {
    //https://stackoverflow.com/questions/44917513/passing-an-additional-parameter-with-an-onchange-event
    this.setState({isUserLoading: true});
    var _this = this;
    
    $.getJSON('https://'+this.state.wikipedia+'.wikipedia.org/w/api.php?callback=?', {
            action: 'query',
            list: 'prefixsearch',
            format: 'json',
            psnamespace: 2,
            pssearch: 'User:'+query
          }, function(data) {
            const results = data.query.prefixsearch.map(elem => {
                return elem.title.split('/')[0].substr(elem.title.indexOf(':') + 1);
            });
            let unique = [...new Set(results)];
            _this.setState({
              isUserLoading: false,
              optionsAsync: unique,
            });
            
    });
  }
  
  _handleArticleSearch = (query) => {
    //https://stackoverflow.com/questions/44917513/passing-an-additional-parameter-with-an-onchange-event
    this.setState({isArticleLoading: true});
    var _this = this;
    
    $.getJSON('https://'+this.state.wikipedia+'.wikipedia.org/w/api.php?callback=?', {
            action: 'query',
            list: 'prefixsearch',
            format: 'json',
            psnamespace: 0,
            pssearch: query
          }, function(data) {
            const results = data.query.prefixsearch.map(elem => {
                return elem.title.split('/')[0];
            });
            //console.log(results);
            let unique = [...new Set(results)];
            _this.setState({
              isArticleLoading: false,
              optionsAsync: unique,
            });
            
    });
  }
  
  render() {
    const languages = ['et','lv'];//defaultā lang jāieliek state no localstorage
    
    return (
      <div className="container">
        {/* rindas beigas */}
        <div className="row">
        <div className="col-md-2">
        <label>Wikipedia</label>
        <select onChange={this.handleInput} name="wikipedia" className="form-control">
            {languages.map(item => <option key={item}>{item}</option>)}
        </select>
        </div>
        <div className="col-md-7">
        <label>Article</label>
        <AsyncTypeahead
          multiple={false}
          options={this.state.optionsAsync}
          placeholder="October"
          minLength={3}
          onChange={(selected) => {
            this.setState({articleName: selected[0]})
          }}
          isLoading={this.state.isArticleLoading}
          onSearch={this._handleArticleSearch} />
        </div>
        <div className="col-md-3">
        <label>User</label>
        <AsyncTypeahead
          multiple={false}
          options={this.state.optionsAsync}
          onChange={(selected) => {
            this.setState({userName: selected[0]})
          }}
          placeholder="ExampleUser"
          minLength={3}
          isLoading={this.state.isUserLoading}
          onSearch={this._handleUserSearch} />
        </div>
        </div>
        <br />
        {/* rindas beigas */}
        <div className="row">
        <div className="col-md-2">
        <label>
        <span>New article</span>
        <br/>
        <Toggle defaultChecked={this.state.newArticle} onChange={this.handleNewChange} />
        </label>
        </div>
        <div className="col-md-2">
        <label>
        <span>Images</span>
        <br/>
        <Toggle defaultChecked={this.state.imageCount} onChange={this.handleImageChange} />
        </label>
        </div>
        <div className="col-md-2">
        <label>
        <span>Wikidata</span>
        <br/>
        <Toggle defaultChecked={this.state.wikidataCount} onChange={this.handleWikidataChange} />
        </label>
        </div>
        </div>
        <br />
        {/* rindas beigas */}
        <div className="row">
        <div className="col-md-3">
        <label>Article size</label>
        <NumericInput className="form-control" strict value={this.state.articleSize} min={ 0 } max={ 150 } step={ 1 } precision={ 0 } size={ 1 } mobile inputMode="numeric" onChange={this.handleSizeChange} />
        </div>
        </div>
        <br />
        {/* rindas beigas */}
        <PointsSum wd={this.state.wikidataCount} news={this.state.newArticle} imgs={this.state.imageCount} size={this.state.articleSize} />
        <br />
        <button type="button" className="btn btn-primary" onClick={this.saveToDB}>Save!</button>
      </div>
    );
  }
}
// disabled={this.state.isDisabled}
//<PointsSum wd={this.state.wikidataCount} news={this.state.newArticle} imgs={this.state.imageCount} size={this.state.number} />
//<p>I have selected: {this.state.userName}</p>

export default New;