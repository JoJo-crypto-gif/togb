$(document).ready(function(){
    $('#add-member-form').on('submit', function(e){
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'insert_member.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                var result = JSON.parse(response);
                alert(result.message);
                if (result.status === 'success') {
                    $('#add-member-form')[0].reset();
                }
            },
            error: function() {
                alert('An error occurred while adding the member.');
            }
        });
    });
});
