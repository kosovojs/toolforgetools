<!DOCTYPE html>
<html>
  <head>
    <script
      type="text/javascript"
      src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/3.3.1/jquery.min.js"
    ></script>
    <link
      rel="stylesheet"
      type="text/css"
      href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"
    />
    <script
      type="text/javascript"
      charset="utf8"
      src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/datatables/1.10.16/js/jquery.dataTables.min.js"
    ></script>
    <link
      rel="stylesheet"
      type="text/css"
      href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/datatables/1.10.16/css/jquery.dataTables.min.css"
    />
    <title>CEE Spring 2020</title>
    <meta charset="utf-8" />
  </head>
  <body>
    <div>
	New timestamp:
	<input id="new-time"></input>
	<button class="btn" onclick="savetime()">Saglabāt</button>
	<hr />
      <table class="table table-striped" id="exampleTable">
        <thead>
          <tr>
            <th width='200px'>Raksts</th>
            <th width='100px'>Timestamp</th>
            <th width='35%'>Kategorijas</th>
            <th width='35%'>Veidnes</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <script>

	function savetime() {
		var value = $('#new-time').val();
		console.log(value)

        $.ajax({
          type: "post",
          url: "save.php",
          dataType: "json",
		  data: {value}
        })  .done(function(resp) {
			console.log(resp)
  })

	}
      function replaceAll(str, find, replace) {
        return str.replace(new RegExp(find, "g"), replace);
      }

      function create_lv_link(href, label) {
        return (
          '<a target="_blank" href="https://lv.wikipedia.org/wiki/' +
          replaceAll(href, " ", "_") +
          '">' +
          replaceAll(label, "_", " ") +
          "</a>"
        );
      }

      var table;

      function add_tables(finalmas) {
        var iTableCounter = 1;
        var detailsTableHtml;

        $(document).ready(function() {
          detailsTableHtml = $("#detailsTable").html();

          table = $("#exampleTable").DataTable({
            aaData: finalmas,
			"order": [[ 1, "asc" ]],
            columnDefs: [
              {
                render: function(data) {
                  if (data) {
                    return create_lv_link(data, data);
                  } else {
                    return "";
                  }
                },
                targets: 0
              },
              {
                targets: [1,2,3],
                render: function(data) {
                  return data;
                }
              }
            ],
            lengthMenu: [
              [-1, 50, 100],
              ["All", 50, 100]
            ],
            bPaginate: true,
            aoColumns: [
              { mDataProp: "title" },
              { mDataProp: "timestamp" },
              { mDataProp: "cats" },
              { mDataProp: "tpls" }
            ]
          });
        });
      }

      function regenerate(new_d) {
        $.ajax({
          type: "get",
          url: "api.php",
          dataType: "json"
        })
          .done(function(response) {
			  
              add_tables(response['data']);
			  
          })
          .fail(function(XMLHttpRequest, textStatus, errorThrown) {
            console.log("Status: " + textStatus);
            console.log("Error: " + errorThrown);
            console.log(XMLHttpRequest["responseText"]);
          });
      }

      regenerate(true);
    </script>
  </body>
</html>
