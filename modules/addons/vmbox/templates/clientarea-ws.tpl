<style>
  #vmbIframe {
    width: 100%;
    min-height: 600px;
    border: none;
    box-shadow: 0px 0px 8px 4px rgb(232, 232, 234);
  }
</style>
<iframe id="vmbIframe" src=""></iframe>

<script>
  function iResize() {
    try {
      document.getElementById("vmbIframe").style.height =
        document.getElementById("vmbIframe").contentWindow.document.body
          .offsetHeight + "px";
    } catch (e) {}
  }
  setInterval(iResize, 1000);
  document.getElementById("vmbIframe").src = window.location.href + "&panel=1";
</script>
