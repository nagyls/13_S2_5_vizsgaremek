Param(
  [string]$BaseUrl = "http://127.0.0.1:8000"
)

$ErrorActionPreference = "Stop"

$collectionSmoke401 = (Get-ChildItem "postman/collections" -Filter "*api.postman_collection.json" | Select-Object -First 1).FullName
$envSmoke = (Get-ChildItem "postman/environments" -Filter "*api.postman_environment.json" | Select-Object -First 1).FullName

$collectionBusiness = (Get-ChildItem "postman/collections" -Filter "*api 2.postman_collection.json" | Select-Object -First 1).FullName
$envBusiness = (Get-ChildItem "postman/environments" -Filter "*api 2.postman_environment.json" | Select-Object -First 1).FullName

if (-not $collectionBusiness -or -not $envBusiness) {
  throw "Business collection or environment file not found."
}

# Uncomment below for 401 (unauthorized) tests without token:
# Write-Host "Running smoke collection (expecting 401-s)..." -ForegroundColor Cyan
# npx newman run $collectionSmoke401 -e $envSmoke --env-var "base_url=$BaseUrl"

Write-Host "Running business-flow collection (200 OK with authentication)..." -ForegroundColor Cyan
npx newman run $collectionBusiness -e $envBusiness --env-var "base_url=$BaseUrl"

Write-Host "Done." -ForegroundColor Green
