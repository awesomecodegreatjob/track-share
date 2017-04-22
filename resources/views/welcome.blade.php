@extends('templates.main')

@section('body')
   <div class="row">
      <div class="medium-6 large-4 medium-offset-3 large-offset-4">
         <form action="search">
            <label for="url">Add Your Music URL <i class="icon-link"></i><br/><br/></label>
            <input type="text" name="url" placeholder="Google Music or Spotify link">
            <br/><br/>
            <input type="submit" class="button small expand" value="SHARE">
         </form>
      </div>
   </div>
 @endsection
