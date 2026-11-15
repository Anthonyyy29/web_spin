<x-layouts.dashboard :navmenu="$navmenu">


<div class="modal fade" id="editPodcastModal" tabindex="-1" aria-labelledby="editPodcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTitle">Edit Podcast</h4>
            </div>
            <div class="modal-body">
                <form id="editPodcastForm" class="needs-validation" novalidate method="post" action="/dashboard/podcast/save" enctype="multipart/form-data">
@csrf
                    <input type="hidden" id="podcastId" name="id">

                    <div class="mb-3 text-center">
                        <img id="previewImage" src="" alt="No Image" class="img-thumbnail" style="width: 200px; height: 200px; object-fit: cover;">
                    </div>

                    <div class="mb-3">
                        <label for="podcastImage" class="form-label">Podcast Image</label>
                        <p class="text-primary" style="font-size: smaller; font-style:italic">Max 1mb, jpg/jpeg only, 512x512</p>
                        <input type="file" class="form-control" id="podcastImage" name="image" accept="image/*" required>
                        <div class="invalid-feedback">Please upload a file.</div>
                    </div>

                    <div class="mb-3">
                        <label for="podcastTitle" class="form-label">Podcast Title</label>
                        <input type="text" class="form-control" id="podcastTitle" name="title" required>
                        <div class="invalid-feedback">Please enter your title.</div>
                    </div>

                    <div class="mb-3">
                        <label for="podcastDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="podcastDescription" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback">Please enter your description.</div>
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
            <button onclick="changeTitle('Create Podcast',true)" class="btn btn-success edit-podcast">Create new podcast</button>
        </div>
        <div class="col-12">
            <div class="callout callout-info">
                Use the Create new podcast to upload your podcast episode with accurate information.
            </div>
        </div>
        @foreach ($contents as $podcast)
            <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-3">
                <div class="card m-1 h-100">
                    <img class="card-img-top" src="/assets/uploads/{{ $podcast['image'] }}" style="object-fit: cover;" alt="Card image cap">
                    <div class="card-body">
                        <p class="card-text"><b>{{ $podcast['title'] }}</b></p>
                    </div>
                    <div class="card-footer d-flex justify-content-center">
                    <div class="btn-group" role="group">
                            <button onclick="location.href='/dashboard/podcast/episode/open/{{ $podcast['id'] }}'" class="btn btn-primary ">Open Episodes</button>
                            <button onclick="changeTitle('Edit Podcast',false)" class="btn btn-warning edit-podcast"
                                data-id="{{ $podcast['id'] }}"
                                data-title="{{ $podcast['title'] }}"
                                data-description="{{ $podcast['description'] }}"
                                data-image="{{ $podcast['image'] }}">
                                Edit
                            </button>
                            <button onclick="deleteConfirmation({{ $podcast['id'] }})" class="btn btn-danger">Delete</button>
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
        @if ($contentId = session('openContentId'))
            const targetButton = document.querySelector('.edit-podcast[data-id="{{ $contentId }}"]');

            if (targetButton) {
                document.getElementById("podcastId").value = targetButton.getAttribute("data-id");
                document.getElementById("podcastTitle").value = targetButton.getAttribute("data-title");
                document.getElementById("podcastDescription").value = targetButton.getAttribute("data-description");

                let imageSrc = "/assets/uploads/" + targetButton.getAttribute("data-image");
                document.getElementById("previewImage").src = imageSrc;

                let modal = new bootstrap.Modal(document.getElementById("editPodcastModal"));
                modal.show();
            }
        @endif

        document.querySelectorAll(".edit-podcast").forEach(button => {
            button.addEventListener("click", function(event) {
                event.preventDefault();

                document.getElementById("podcastId").value = this.getAttribute("data-id");
                document.getElementById("podcastTitle").value = this.getAttribute("data-title");
                document.getElementById("podcastDescription").value = this.getAttribute("data-description");

                let imageSrc = "/assets/uploads/" + this.getAttribute("data-image");
                document.getElementById("previewImage").src = imageSrc;

                let modal = new bootstrap.Modal(document.getElementById("editPodcastModal"));
                modal.show();
            });
        });

        document.getElementById("podcastImage").addEventListener("change", function(event) {
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
        let input = document.getElementById("podcastImage");

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
        let deleteUrl = "/dashboard/podcast/delete/" + id;

        if (confirm("Are you sure you want to delete this podcast?")) {
            window.location.href = deleteUrl;
        }
    }
</script>
</x-layouts.dashboard>