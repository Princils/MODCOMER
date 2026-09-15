// Call the dataTables jQuery plugin
$(document).ready(function() {
  $('#dataTable').DataTable({
    language: {
      url: 'vista/vendor/datatables/es-MX.json'
    }
  });
});
