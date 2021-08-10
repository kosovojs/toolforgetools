import React, { Component } from 'react';

class WpLink extends Component {
    render() {
      const { project, label, query } = this.props;
      
      //<WpLink label={d.name} project={"lv"} query={"User:"+d.name}/>
      return <GeneralLink linkStart={"https://"+project+".wikipedia.org/wiki/"} query={replaceAll(query,' ', "_")} label={label}/>
    }
  }
  
  var replaceAll = (str, find, replace) => { return str.replace(new RegExp(find, 'g'), replace); }
  
  //
  class GeneralLink extends Component {
  
    render() {
      const { linkStart, query,label } = this.props;
      const urlEncoded = encodeURIComponent(query);
      const text = replaceAll(label,'_', " ");
      
      return <a target="_blank" href={linkStart + urlEncoded}>{text}</a>
    }
  }
  
  class Lvwiki extends Component {
    render() {
      const { article, original, language } = this.props;
      
      if (article==="") {
          return <span><WpLink label={original} project={language}/> (nav iztulkots; <GeneralLink linkStart={"https://www.google.com/search?q="} query={original} label={"Google"}/>)</span>
      } else {
          const lvwikiarticles = article.split("|")
            .map(t => <WpLink label={t} project={"lv"}/>)
            .reduce((prev, curr) => [prev, ', ', curr])
            
          return <span>{lvwikiarticles} (<GeneralLink linkStart={"https://www.google.com/search?q="} query={original} label={"Google"}/>)</span>
      }
    }
}

export {WpLink, GeneralLink};