<x-layouts.dashboard :navmenu="$navmenu">


<div class="modal fade" id="editSponsorModal" tabindex="-1" aria-labelledby="editSponsorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Edit Sponsor</h4>
            </div>
            <div class="modal-body">
                <form id="editSponsorForm" class="needs-validation" novalidate method="post" action="/dashboard/sponsor/save" enctype="multipart/form-data">
@csrf
                    <input type="hidden" id="SponsorId" name="id">

                    <div class="mb-3 text-center">
                        <img id="previewImage" src="" alt="No Image" class="img-thumbnail" style="width: 400px; height: 250px; object-fit: fill;">
                    </div>

                    <div class="mb-3">
                        <label for="SponsorImage" class="form-label">Sponsor Image</label>
                        <p class="text-primary" style="font-size: smaller; font-style:italic">Max 1mb, jpg/jpeg only, 512x512</p>
                        <input type="file" class="form-control" id="SponsorImage" name="image" accept="image/*" required>
                        <div class="invalid-feedback">Please upload a file.</div>
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
            <button onclick="changeTitle('Create Sponsor',true)" class="btn btn-success edit-sponsor">Create new Sponsor</button>
        </div>
        <div class="col-12">
            <div class="callout callout-info">
                Use the Create new sponsor to upload your sponsor.
            </div>
        </div>
        @foreach ($sponsors as $sponsor)
            <div class="col-sm-3 col-6">
                <div class="card m-1 h-100">
                    <img class="card-img-top" src="/assets/uploads/{{ $sponsor['image'] }}" style="object-fit: cover;" alt="Card image cap">
                    <div class="card-body">
                        
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end">
                            <button onclick="deleteConfirmation({{ $sponsor['id'] }})" class="btn btn-danger mx-1">Delete</button>
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
        document.querySelectorAll(".edit-sponsor").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();          
                let modal = new bootstrap.Modal(document.getElementById("editSponsorModal"));
                modal.show();
            });
        });
        document.getElementById("SponsorImage").addEventListener("change", function(event) {
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
        let input = document.getElementById("SponsorImage");

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
        let deleteUrl = "/dashboard/sponsor/delete/" + id;

        if (confirm("Are you sure you want to delete this Sponsor?")) {
            window.location.href = deleteUrl;
        }
    }
</script>
</x-layouts.dashboard>