@extends('layouts.app')

@section('content')
	<div class="container">
	<form action="{{ route('search')}}">
	  <div class="form-row">
	    <div class="form-group col-sm-1">
	      <label for="input1">Seires</label>
	      <input type="text" class="form-control" id="input1" placeholder="Series" name="series">
	    </div>
	    <div class="form-group col-sm-2">
	      <label for="input2">Number</label>
	      <input type="text" class="form-control" id="input2" placeholder="Number" name="number">
	    </div>
	  </div>
	  <button type="submit" class="btn btn-primary">Search</button>
	</form>
	</div>
@endsection