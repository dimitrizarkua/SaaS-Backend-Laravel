<div>{{$address['addressLine']}}</div>
@if(isset($address['suburbAndState']))
    <div style="text-transform: uppercase">{{$address['suburbAndState']}}</div>
    <div style="text-transform: uppercase">{{$address['country']}}</div>
@endif
