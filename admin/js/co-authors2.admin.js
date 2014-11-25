jQuery(document).ready(function($){
  var users = $.parseJSON(ca2.users);
  var substringMatcher = function(strs) {
    return function findMatches(q, cb) {
      var matches, substrRegex;

      // an array that will be populated with substring matches
      matches = [];

      // regex used to determine if a string contains the substring `q`
      substrRegex = new RegExp(q, 'i');

      // iterate through the pool of strings and for any string that
      // contains the substring `q`, add it to the `matches` array
      $.each(strs, function(i, str) {
        if (substrRegex.test(str.user_name)) {
          // the typeahead jQuery plugin expects suggestions to a
          // JavaScript object, refer to typeahead docs for more info
          matches.push({ value: str.user_name, user_id: str.user_id });
        }
      });

      cb(matches);
    };
  };

  // initialize the typeahead plugin
  $('#ca2_search_authors .typeahead').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
  },
  {
    name: 'users',
    displayKey: 'value',
    source: substringMatcher(users)
  }).on('typeahead:selected', function(event,data){
    $('#ca2_search_authors').append('<p>'+data.value+'<input type="hidden" value="'+data.user_id+'" name="ca2_post_authors[]" class="ca2_post_authors"> <a class="ca2_remove_author">Remove</a></p>');
    $(this).val('');
  });

  // remove the author from the post when the "remove" link is selected
  $(document).on('click','.ca2_remove_author',function(){
    $(this).parent().remove();
  });

  // make the post's actual author the first co-author selected. This way the wrong author won't show up in the post if plugin is removed or de-activated
  $('form#post').on('submit',function(){
    var first_coauthor = $('input.ca2_post_authors:first').val();
    $('#post_author_override').val(first_coauthor);
  });
});