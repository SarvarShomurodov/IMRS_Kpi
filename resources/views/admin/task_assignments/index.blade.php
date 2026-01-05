@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>TaskAssignlarni qo'shish</h2>
        </div>
        <div class="pull-right">
          @can('create-taskassign')
            <a class="btn btn-primary mb-2" href="{{ route('admin.task_assignments.create') }}">TaskAssign qo'shish</a>
          @endcan
        </div>
    </div>
</div>

@if (session('success'))
  <div class="alert alert-success">
      {{ session('success') }}
  </div>
@endif

<!-- Filter Section -->
<div class="card mb-3">
  
    <div class="card-body">
        <form method="GET" action="{{ route('admin.task_assignments.index') }}" id="filter-form">
            <div class="row">
                <!-- Employee Name Filter with Autocomplete -->
                <div class="col-md-3 position-relative">
                    <label for="employee_name" class="form-label">Xodim ismi:</label>
                    <input type="text" 
                           name="employee_name" 
                           id="employee_name" 
                           class="form-control" 
                           placeholder="Xodim ismini kiriting..."
                           value="{{ request('employee_name') }}"
                           autocomplete="off">
                    <div id="employee-suggestions" class="dropdown-menu" style="display: none; position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; width: 100%;">
                        <!-- Autocomplete suggestions will appear here -->
                    </div>
                </div>
				<!-- Monthly Report Filter -->
                <div class="col-md-3">
                    <label for="month_filter" class="form-label">Oylik xisobot:</label>
                    <select name="month_filter" id="month_filter" class="form-select">
                        <option value="">Oy tanlang</option>
                        <option value="january" {{ request('month_filter') == 'january' ? 'selected' : '' }}>Yanvar</option>
                        <option value="february" {{ request('month_filter') == 'february' ? 'selected' : '' }}>Fevral</option>
                        <option value="march" {{ request('month_filter') == 'march' ? 'selected' : '' }}>Mart</option>
                        <option value="april" {{ request('month_filter') == 'april' ? 'selected' : '' }}>Aprel</option>
                        <option value="may" {{ request('month_filter') == 'may' ? 'selected' : '' }}>May</option>
                        <option value="june" {{ request('month_filter') == 'june' ? 'selected' : '' }}>Iyun</option>
                        <option value="july" {{ request('month_filter') == 'july' ? 'selected' : '' }}>Iyul</option>
                        <option value="august" {{ request('month_filter') == 'august' ? 'selected' : '' }}>Avgust</option>
                        <option value="september" {{ request('month_filter') == 'september' ? 'selected' : '' }}>Sentabr</option>
                        <option value="october" {{ request('month_filter') == 'october' ? 'selected' : '' }}>Oktabr</option>
                        <option value="november" {{ request('month_filter') == 'november' ? 'selected' : '' }}>Noyabr</option>
                        <option value="december" {{ request('month_filter') == 'december' ? 'selected' : '' }}>Dekabr</option>
                    </select>
                </div>
                <!-- Date Range Filter -->
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Boshlanish sanasi:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" 
                           value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="end_date" class="form-label">Tugash sanasi:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" 
                           value="{{ request('end_date') }}">
                </div>

                <!-- Filter Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex flex-column gap-1">
                        <button type="submit" class="btn btn-info btn-sm">Filter</button>
                        <a href="{{ route('admin.task_assignments.index') }}" class="btn btn-secondary btn-sm">Tozalash</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<form action="{{ route('admin.task_assignments.massDelete') }}" method="POST" id="mass-delete-form">
    @csrf
    @method('DELETE')

<div class="card">
    <div class="card-body">
        <div class="mb-2">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Tanlangan ma\'lumotlarni o'chirishni istaysizmi?')">
              Tanlanganlarni o'chirish
          </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="myTable3">
            <thead>
                <tr>
                <th><input type="checkbox" id="select-all"></th>
                <th scope="col">№</th>
                <th scope="col">FISH</th>
                <th scope="col">Vazifalar</th>
                <th scope="col">Baxo</th>
                <th scope="col">Sana</th>
                <th scope="col">Comment</th>
                <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $assignment)
                <tr>
                    <td><input type="checkbox" name="ids[]" value="{{ $assignment->id }}"></td>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $assignment->user?->firstName }} {{ $assignment->user?->lastName }}</td>
                    <td>{{ $assignment->subtask->title }} ({{ $assignment->subtask->max }} - {{ $assignment->subtask->min }})</td>
                    <td>{{ $assignment->rating }}</td>
                    <td>{{ $assignment->addDate }}</td>
                    <td>{{ $assignment->comment }}</td>
                    <td>
                        <form action="{{ route('admin.task_assignments.destroy', $assignment->id) }}" method="post" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            @can('edit-subtask')
                                <a href="{{ route('admin.task_assignments.edit', $assignment->id) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                            @endcan

                            @can('delete-subtask')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Do you want to delete this project?');"><i class="bi bi-trash"></i> Delete</button>
                            @endcan
                        </form>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            <span class="text-danger">
                                <strong>Hech qanday ma'lumot topilmadi!</strong>
                            </span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>
</div>
</form>

<style>
#employee-suggestions {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 1px;
    max-width: 100%;
}

#employee-suggestions .dropdown-item {
    display: block;
    padding: 8px 12px;
    color: #333;
    text-decoration: none;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
}

#employee-suggestions .dropdown-item:hover,
#employee-suggestions .dropdown-item.active {
    background-color: #f8f9fa;
    color: #0d6efd;
}

#employee-suggestions .dropdown-item:last-child {
    border-bottom: none;
}

.position-relative {
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Employee autocomplete functionality
    const employeeInput = document.getElementById('employee_name');
    const suggestionsDiv = document.getElementById('employee-suggestions');
    
    // Employee data from server
    const employees = @json($employees ?? []);
    
    let currentFocus = -1;
    
    employeeInput.addEventListener('input', function() {
        const inputValue = this.value.toLowerCase().trim();
        
        if (inputValue.length === 0) {
            hideSuggestions();
            return;
        }
        
        // Filter employees based on input
        const filteredEmployees = employees.filter(employee => {
            const fullName = `${employee.firstName} ${employee.lastName}`.toLowerCase();
            return fullName.includes(inputValue);
        });
        
        showSuggestions(filteredEmployees, inputValue);
    });
    
    // Handle keyboard navigation
    employeeInput.addEventListener('keydown', function(e) {
        const suggestions = suggestionsDiv.querySelectorAll('.dropdown-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= suggestions.length) currentFocus = 0;
            setActiveSuggestion(suggestions);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = suggestions.length - 1;
            setActiveSuggestion(suggestions);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus > -1 && suggestions[currentFocus]) {
                selectEmployee(suggestions[currentFocus].getAttribute('data-name'));
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });
    
    function showSuggestions(employees, searchTerm) {
        if (employees.length === 0) {
            hideSuggestions();
            return;
        }
        
        let html = '';
        employees.slice(0, 10).forEach(employee => { // Limit to 10 suggestions
            const fullName = `${employee.firstName} ${employee.lastName}`;
            const highlightedName = highlightMatch(fullName, searchTerm);
            html += `<a href="#" class="dropdown-item" data-name="${fullName}">${highlightedName}</a>`;
        });
        
        suggestionsDiv.innerHTML = html;
        suggestionsDiv.style.display = 'block';
        
        // Add click event listeners
        suggestionsDiv.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                selectEmployee(this.getAttribute('data-name'));
            });
        });
        
        currentFocus = -1;
    }
    
    function hideSuggestions() {
        suggestionsDiv.style.display = 'none';
        currentFocus = -1;
    }
    
    function setActiveSuggestion(suggestions) {
        suggestions.forEach((item, index) => {
            if (index === currentFocus) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    function selectEmployee(fullName) {
        employeeInput.value = fullName;
        hideSuggestions();
        employeeInput.focus();
    }
    
    function highlightMatch(text, searchTerm) {
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!employeeInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            hideSuggestions();
        }
    });

    // Select all checkbox functionality
    document.getElementById('select-all').onclick = function () {
        let checkboxes = document.querySelectorAll('input[name="ids[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    };

    // Filter conflicts prevention
    // Oylik filter tanlanganda sana filterlarini tozalash
    document.getElementById('month_filter').addEventListener('change', function() {
        if (this.value) {
            document.getElementById('start_date').value = '';
            document.getElementById('end_date').value = '';
        }
    });

    // Sana filterlari tanlanganda oylik filterni tozalash
    document.getElementById('start_date').addEventListener('change', function() {
        if (this.value) {
            document.getElementById('month_filter').value = '';
        }
    });

    document.getElementById('end_date').addEventListener('change', function() {
        if (this.value) {
            document.getElementById('month_filter').value = '';
        }
    });
});
</script>
@endsection