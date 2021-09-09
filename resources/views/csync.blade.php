<h5>{{ $c }} records has been created.</h5>
<h5>{{ $u }} records has been updated.</h5>
<h5><a id="clink" href="http://127.0.0.1:8800/get-outreach-records/{{ $i + 1 }}">Continue</a></h5>

<script type="text/javascript">
setTimeout(function(){ document.getElementById("clink").click(); }, 3000);
</script>