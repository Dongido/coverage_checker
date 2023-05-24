<div>
  <x-loader />
  <div class="intro">
    <div class="mask d-flex align-items-center h-100">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-12 logo">
            <img src="img/fob_logo.png" class="img-responsive" alt="fob logo">
          </div>
          <div class="col-xl-7 col-md-8">
            <form class="bg-white  rounded-5 shadow-5-strong p-5">
              <div>
                  @if (session()->has('message'))
                      <div class="alert alert-primary" role="alert">
                          {{ session('message') }}
                      </div>
                  @endif
              </div>

              <div class="form-outline mb-4">
                <label class="form-label">Enter Address</label>
                <input type="text" wire:model.debounce.150ms="address" class="form-control" placeholder="Insert full address" />
              </div>

              @if($isLocated)
              <div class="form-outline mb-4">
                <label class="form-label">Choose Your Specific Location</label>
                <select wire:model="selectedaddress" class="form-control">
                  <option value="">---SELECT LOCATION---</option>
                  @foreach($geoAddress as $add)
                    <option value="{{$add['lat'].'-'.$add['lon']}}"> {{$add['display_name']}} </option>
                  @endforeach
                </select>
              </div>
              @endif

              @if(!$isLocated)
                  <button wire:click="convertAddress" type="button" class="btn btn-primary btn-block">Continue</button>
              @endif

              @if($isLocated)
                  <button wire:click="location" type="button" class="btn btn-primary btn-block">Confirm Coverage</button>
              @endif

              @if (session()->has('message'))
                <div class="row mt-3 form_details">
                  <div class="col-md-9">
                    <div class="left_details">
                      <button type="button" data-toggle="modal" data-target="#userInfoModal">I need more information about your network coverage?</button>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="right_details">
                      <button wire:click="refresh" type="button"><i class="fa fa-refresh"></i> Refresh</button>
                    </div>
                  </div>
                </div>  
              @endif
                                          
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="userInfoModal" tabindex="-1" role="dialog" aria-labelledby="userInfoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Enter Your Details</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form wire:submit.prevent="storeContact">
          <div class="modal-body">
            <div>
                @if($errorMessage != '')
                    <div class="alert alert-danger" role="alert">
                        {{ $errorMessage }}
                    </div>
                @endif
            </div>
            <div class="form-row">
              <div class="col-md-12 mb-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroupPrepend"><i style="padding-bottom: 8px;" class="fa fa-user"></i></span>
                  </div>
                  <input wire:model.defer="fullname" type="text" class="form-control" placeholder="Full name" aria-describedby="inputGroupPrepend" required>
                </div>
              </div>
              <div class="col-md-12 mb-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroupPrepend">@</span>
                  </div>
                  <input wire:model.defer="email" type="text" class="form-control" placeholder="Email" aria-describedby="inputGroupPrepend" required>
                </div>
              </div>
              <div class="col-md-12 mb-3">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text" id="inputGroupPrepend"><i class="fa fa-phone" style="padding-bottom: 8px;"></i></span>
                  </div>
                  <input wire:model.defer="phone" type="text" class="form-control" placeholder="Phone number" aria-describedby="inputGroupPrepend" required>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>