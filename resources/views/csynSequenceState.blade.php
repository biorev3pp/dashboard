<h5>{{ $u }} records have been updated.</h5>
<h5>{{ $c }} records have been created.</h5>
<h5>{{ $id }} is last ID.</h5>

<h5><a id="clink" href="http://127.0.0.1:8888/get-sequence-states/{{ $i + 1 }}">Continue</a></h5>

<script type="text/javascript">
setTimeout(function(){ document.getElementById("clink").click(); }, 3000);
</script>