import React, { Component } from 'react';
import ReactTable from "react-table";
import 'react-table/react-table.css';

class WpLink extends Component {
  render() {
    const { project, label } = this.props;
	
	return <GeneralLink linkStart={"https://"+project+".wikipedia.org/wiki/"} query={replaceAll(label,' ', "_")} label={label}/>
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

const columns = [
  {
    Header: "Latviski",
    accessor: d => <Lvwiki key={d.wd} article={d.lv} original={d.orig} language={d.lang}/>,
    id: "lv"
  },
  {
    Header: "Citā valodā",
    accessor: d => <WpLink label={d.orig} project={d.lang}/>,
    id: "orig"
  },
  {
    Header: "Apraksts",
    accessor: d => d.descr,
    id: "desc",
    width: 600
  },
  {
    Header: "iw",
    accessor: d => d.iws,
    id: "iws",
    width: 75
  },
  {
    Header: "Vikidati",
    accessor: d => <GeneralLink linkStart={"https://www.wikidata.org/wiki/"} query={d.wd} label={d.wd}/>,
    id: "wd",
    width: 125
  }
];

class Table extends Component {
  constructor() {
    super();
    this.state = {
      data: [],
	  isLoaded: false,
	  error: false
    };
  }
  
  componentDidMount() {
    fetch("http://tools.wmflabs.org/edgars/missing/src/components/api.php")
      .then(res => res.json() )
      .then(
        (result) => {
          console.log(result);
		  this.setState({
            isLoaded: true,
            data: result
          });
        },
        (error) => {
          this.setState({
            isLoaded: true,
            error
          });
        }
      )
  }
  
  render() {
	const { error, isLoaded, data } = this.state;
    let whatToRender;
	
	if (error) {
      whatToRender = <div>Error: {error.message}</div>;
    } else if (!isLoaded) {
      whatToRender = <div className="alert alert-warning" role="alert">  Uzgaidi brīdi, tiek ielādēti dati!</div>;
    } else {
      whatToRender = <ReactTable
          data={data}
          columns={columns}
		  pageSizeOptions= {[50, 100, 500, 1000]}
          defaultPageSize={50}
          showPagination={true}
          minRows={0}
		  defaultSorted={[
            {
              id: "iw",
              desc: false
            }
          ]}
          className="-striped -highlight"
        />
    }
	
	return (
      <div>
		{whatToRender}
      </div>
    );
  }
}

export default Table;