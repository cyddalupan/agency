<script>
function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}
</script>
<iframe src="<?php echo $route; ?>" onload='javascript:resizeIframe(this);' frameborder="0" scrolling="no" style="width:100%"></iframe>