<x-layouts.dashboard :navmenu="$navmenu">


<div class="modal fade" id="editCrewModal" tabindex="-1" aria-labelledby="editCrewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Edit Crew</h4>
            </div>
            <div class="modal-body">
                <form id="editCrewForm" class="needs-validation" novalidate method="post" action="/dashboard/crew/save" enctype="multipart/form-data">
@csrf
                    <input type="hidden" id="CrewId" name="id">

                    <div class="mb-3 text-center">
                        <img id="previewImage" src="" alt="No Image"
                            class="img-thumbnail"
                            style="width: 319px; height: 508px; object-fit: cover;"></img>

                    </div>

                    <div class="mb-3">
                        <label for="CrewImage" class="form-label">Crew Image</label>
                        <p class="text-primary" style="font-size: smaller; font-style:italic">Max 1mb, jpg/jpeg only, 512x512</p>
                        <input type="file" class="form-control" id="CrewImage" name="image" accept="image/*" required>
                        <div class="invalid-feedback">Please upload a file.</div>
                    </div>

                    <div class="mb-3">
                        <label for="CrewName" class="form-label">Crew Name</label>
                        <input type="text" class="form-control" id="CrewName" name="name" required>
                        <div class="invalid-feedback">Please enter your name.</div>
                    </div>

                    <div class="mb-3">
                        <label for="CrewRole" class="form-label">Crew Role</label>
                        <select class="form-select" id="CrewRole" name="role" required>
                            @foreach ($roleList as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>

                    <div class="mb-3">
                        <label for="CrewAbout" class="form-label">About</label>
                        <textarea class="form-control" id="CrewAbout" name="about" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="CrewInstagramLink" class="form-label">Instagram Link</label>
                        <input type="text" class="form-control" id="CrewInstagramLink" name="instagram_link">
                    </div>

                    <div class="mb-3">
                        <label for="CrewLinkedinLink" class="form-label">LinkedIn Link</label>
                        <input type="text" class="form-control" id="CrewLinkedinLink" name="linkedin_link">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!--begin::Container-->
<div class="container-fluid">
    <!--begin::Row-->
    <div class="row g-4">
        <div class="col-12 text-end mb-3">
            <button onclick="changeTitle('Create Crew',true)" class="btn btn-success edit-Crew">Create new Crew</button>
        </div>
        <div class="col-12">
            <div class="callout callout-info">
                Use the Create new crew to upload your crew with accurate information.
            </div>
        </div>
        <div class="row justify-content-start mt-4">
            <div class="col-4">
                <select class="form-select" onchange="location = 'crew/filter/' + this.value;">
                    <option value="All" {{ ($activefilter === 'All') ? 'selected' : '' }}>All</option>
                    @foreach ($roleList as $role)
                        <option value="{{ $role }}" {{ ($activefilter === $role) ? 'selected' : '' }}>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>


        @foreach ($crews as $crew)
            <div class="col-lg-2 col-sm-4 col-6">
                <div class="card m-1 h-100">
                    <img class="card-img-top" src="/assets/uploads/{{ $crew['image'] }}" style="object-fit: cover;" alt="Card image cap">
                    <div class="card-body">
                        <p class="card-text"><b>{{ $crew['name'] }}</b></p>
                    </div>
                    <div class="card-footer d-flex justify-content-center justify-content-md-end">
                        <div class="btn-group" role="group">
                            <button onclick="changeTitle('Edit Crew',false)" class="btn btn-warning edit-Crew mx-1"
                                data-id="{{ $crew['id'] }}"
                                data-name="{{ $crew['name'] }}"
                                data-role="{{ $crew['role'] }}"
                                data-about="{{ $crew['about'] }}"
                                data-image="{{ $crew['image'] }}"
                                data-instagramlink="{{ isset($crew['instagram_link']) ? $crew['instagram_link'] : '' }}"
                                data-linkedinlink="{{ isset($crew['linkedin_link']) ? $crew['linkedin_link'] : '' }}">
                                Edit
                            </button>
                            <button onclick="deleteConfirmation({{ $crew['id'] }})" class="btn btn-danger mx-1">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12">
            {{ $pager->links() }}
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {


        document.querySelectorAll(".edit-Crew").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();

                document.getElementById("CrewId").value = this.getAttribute("data-id");
                document.getElementById("CrewName").value = this.getAttribute("data-name");
                document.getElementById("CrewRole").value = this.getAttribute("data-role");
                document.getElementById("CrewAbout").value = this.getAttribute("data-about");
                document.getElementById("CrewInstagramLink").value = this.getAttribute("data-instagramlink");
                document.getElementById("CrewLinkedinLink").value = this.getAttribute("data-linkedinlink");

                let imageSrc = "/assets/uploads/" + this.getAttribute("data-image");
                document.getElementById("previewImage").src = imageSrc;

                let modal = new bootstrap.Modal(document.getElementById("editCrewModal"));
                modal.show();
            });
        });

        document.getElementById("CrewImage").addEventListener("change", function(event) {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("previewImage").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<script>
    function changeTitle(newTitle, isCreate) {
        let titleField = document.getElementById("modalTitle");
        let input = document.getElementById("CrewImage");

        if (titleField) {
            titleField.innerText = newTitle;
        }

        if (input) {
            let invalidFeedback = input.nextElementSibling;

            if (isCreate) {
                input.setAttribute("required", "required");
            } else {
                input.removeAttribute("required");
            }

            if (invalidFeedback && invalidFeedback.classList.contains("invalid-feedback")) {
                invalidFeedback.style.display = isCreate ? "" : "none";
            }

            if (!isCreate) {
                input.classList.remove("is-invalid");
            }
        }
    }


    function deleteConfirmation(id) {
        let deleteUrl = "/dashboard/crew/delete/" + id;

        if (confirm("Are you sure you want to delete this Crew?")) {
            window.location.href = deleteUrl;
        }
    }
</script>
</x-layouts.dashboard>