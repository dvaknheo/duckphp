<script>
function post_to_get()
{
const xhr = new XMLHttpRequest();
xhr.open('POST', 'https://example.com/data.json'); // by default async 
xhr.responseType = 'json'; // in which format you expect the response to be


xhr.onload = function() {
  if(this.status == 200) {// onload called even on 404 etc so check the status
   console.log(this.response); // No need for JSON.parse()
  }
};

xhr.onerror = function() {
  // error 
};


xhr.send();
}
</script>