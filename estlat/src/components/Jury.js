import React, { Component } from 'react';
import ReactTable from "react-table";
import 'react-table/react-table.css';
import { WpLink } from './URLutils';

class Section extends React.Component {
    render() {
      const { data, lang } = this.props;
      
      return (<div>
          <ReactTable
            data={data}
            columns={[
                {
                  Header: "User",
                  accessor: d => <WpLink label={d.user} project={lang} query={"User:"+d.user}/>,
                  id: "name"
                },
                {
                  Header: "Articles",
                  accessor: d => d.articlecount,
                  id: "articles"
                },
                {
                  Header: "Points",
                  accessor: d => d.points,
                  id: "points"
                }
              ]}
            defaultPageSize={10}
            defaultSorted={[
              {
                id: "points",
                desc: true
              }
            ]}
            showPagination={true}
            minRows={0}
            className="-striped -highlight"
            SubComponent={row => {
              return (
                <div style={{ padding: "20px" }}>
                
                  <ReactTable
                    data={row.original.articles}
                    //{"id":"176","title":"Lerojs Fers","added":"2018-07-16 23:42:47","new":"1","size":"2","images":"1","wikidata":"1","sum":5}
                    columns={[
                        {
                          Header: "Article",
                          accessor: d => <WpLink label={d.title} project={lang} query={d.title}/>,
                          id: "name"
                        },
                        {
                          Header: "Added",
                          accessor: d => d.added,
                          id: "added",
                          width: 170
                        },
                        {
                          Header: "Creation",
                          accessor: d => d.new,
                          id: "create",
                          width: 75,
                        },
                        {
                          Header: "Size",
                          accessor: d => d.size,
                          id: "size",
                          width: 75
                        },
                        {
                          Header: "Images",
                          accessor: d => d.images,
                          id: "images",
                          width: 75
                        },
                        {
                          Header: "Wikidata",
                          accessor: d => d.wikidata,
                          id: "wd",
                          width: 75
                        },
                        {
                          Header: "Sum",
                          accessor: d => d.sum,
                          id: "sum",
                          width: 75
                        },
                      ]}
                    defaultPageSize={10}
                    defaultSorted={[
                      {
                        id: "sum",
                        desc: true
                      }
                    ]}
                    minRows={0}
                  />
                </div>
              );
            }}
          />
          </div>
      );
    }
}

//https://github.com/github/fetch/issues/256
//https://stackoverflow.com/questions/316781/how-to-build-query-string-with-javascript/34209399#34209399
function buildUrl(url, parameters){
  var qs = "";
  for(var key in parameters) {
    var value = parameters[key];
    qs += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
  }
  if (qs.length > 0){
    qs = qs.substring(0, qs.length-1); //chop off last "&"
    url = url + "?" + qs;
  }
  return url;
}

class Table extends React.Component {
  constructor() {
    super();
    this.state = {
      data: [],
      isLoaded: false,
      error: false
    };
  }

  componentDidCatch(error, info) {
    console.log(info);
    this.setState({ error: true });
  }
  
  componentDidMount() {
	  fetch(buildUrl("http://localhost/estlat/src/components/api.php",{'act':'main'}))
	  //fetch("http://localhost/estlat/src/components/api.php?act=main")
      .then(res => res.json())
      .then(
        (result) => {
          this.setState({
            isLoaded: true,
            data: result
          });
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          this.setState({
            isLoaded: true,
            error
          });
        }
      )
  }

  render() {
    const { data, isLoaded } = this.state;
    
    if (this.state.error) {
      return <div className="container">Some error occured, please contact <a href="https://lv.wikipedia.org/wiki/Dalībnieka_diskusija:Edgars2007" target="_blank">Edgars2007</a></div>
    } else if (this.state.isLoaded) {
      const etwikisection = data.et.length==0 ? "No articles yet :(" : <Section data={data.et} lang={"et"} />;
      const lvwikisection = data.lv.length==0 ? "No articles yet :(" : <Section data={data.lv} lang={"lv"} />;
      
      return (
        <div className="container">
          <h3>Estonian Wikipedia</h3>
          {etwikisection}
          <h3>Latvian Wikipedia</h3>
          {lvwikisection}
        </div>
      )
    } else {
      return <div className="container">Wait a moment, the data is loading</div>
    }
  }
}


export default Table;