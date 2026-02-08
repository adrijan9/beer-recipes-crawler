# Beer Smith Crawler

This project crawls recipes from BeerSmith and stores them as JSON files. It includes a lightweight preview UI for browsing saved recipe files.

## What It Does

- Crawl BeerSmith recipe search results by term, depth (pages), sort order, rating, and recipe type.
- Save the crawl results to `recipes/` as a JSON file.
- Preview one or more saved files in the browser with merged statistics, filters, and recipes.
Currently only supports crawling recipes from BeerSmith.

Built with PHP for the crawler/API and Vue + Vite + Tailwind CSS for the UI.

## Frontend (Vue + Vite)

The legacy PHP UI now lives in `client_php/`, and the Vue source lives in `client_src/`.

Run the client only:

```bash
chmod +x ./serve.sh
./serve.sh
```

`./serve.sh` installs PHP and JS dependencies if needed, rebuilds the frontend bundle, and starts the PHP server for `/api/*` plus the static client at `http://localhost:8000`.

For development (hot reload):

```bash
npm run dev
```

Keep `./serve.sh` running in one terminal, and in another terminal start the Vite dev server with `npm run dev`.

Build the production bundle (output in `client/`):

```bash
npm run build
```

## Crawl from the CLI

The CLI entrypoint is `crawler` (a wrapper around `main.php`):

```bash
php crawler --term lager --depth 2 --sort "Best Match" --rated 3 --types "allgrain,extract"
```

Options:

- `--term` Search term (default: `lager`)
- `--depth` Number of result pages to crawl (default: `1`)
- `--sort` Sort order (default: `Best Match`)
- `--rated` Rating filter (`0` = Unrated, `1-5` = beer rating)
- `--types` Comma-separated recipe types (for example: `allgrain,extract,mead`)
- `--help` Show CLI usage information

The output JSON is written to `recipes/` with a timestamped filename.

## Crawl from the UI

Use the **Start a Crawl** section in the preview UI, set filters, and start the crawl. The UI supports:

- Search term
- Optional file name (alphanumeric, `-`, `_`)
- Depth
- Sort By
- Rating (0 = Unrated, 1–5)
- Recipe Type (checkboxes)

If you set a file name, it will be used as the JSON file name (no extension required).

## Output Files

JSON files are stored in:

```
recipes/
```

Each file includes:

- Crawl filters (term, depth, sort, rating, recipe type)
- Timestamp
- Requested URLs
- Recipes array
