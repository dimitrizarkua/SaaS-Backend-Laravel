<style>
    * {
        box-sizing: border-box;
        font-family: Liberation-Sans, sans-serif;
    }

    h2, h3 {
        page-break-after: avoid;
    }

    ul li::before {
        list-style: none;
        content: '\02022';
        font-size: 20px;
        vertical-align: middle;
        margin-left: -40px;
        margin-right: 12px;
    }
    ul li.not-symbol::before {
        content: '';
    }

    section {
        page-break-inside: avoid;
        margin-top: 24px;
        margin-bottom: 24px;
        font-size: 10.5pt;
        word-break: normal;
    }

    img {
        max-width: 100% !important;
    }

    ul, img {
        page-break-inside: avoid;
    }

    hr {
        border: none;
        background: transparent;
        height: 2px;
        border-top: #999999 1px solid;
        width: 100%;
    }

    .clearfix::after {
        content: '';
        clear: both;
        display: table;
    }

    .sm-text {
        font-size: 8pt;
    }

    .md-text {
        font-size: 10.5pt;
    }

    .lg-text {
        font-size: 15pt;
    }

    .title {
        margin: 10px 0;
        font-weight: 500;
        font-size: 13pt;
    }

    .document {
        background-color: #fff;
        height: 100%; width: auto;
        padding: 10px 80px;
        font-size: 10.5pt;
    }

    /* Cost summary */
    .cost-summary {}

    .cost-summary__table {
        width: 100%;
    }

    .cost-summary__table tr {
        width: 100%;
    }

    .cost-summary__table tr td {
        padding-top: 3px;
        padding-bottom: 3px;
        text-align: right;
    }

    .cost-summary__prop {}

    .cost-summary__value {
        width: 130px;
        padding-left: 20px;
    }

    /* Numerical list */
    .numerical-list {
        margin-top: 24px;
        margin-bottom: 24px;
        font-size: 10.5pt;
    }

    .numerical-list__list {
        margin-top: 10px;
        margin-left: -10px;
    }

    /* Bullet list */
    .bullet-list {}

    .bullet-list__list {
        list-style: none;
    }

    /* Image */
    .image {
        text-align: center;
    }

    .image__title, .image__description {
        text-align: left;
    }

    .bold {
        font-weight: 600;
    }

    /* Photos */
    .photos {
        margin-top: 24px;
        margin-bottom: 24px;
    }

    .photos__item {
        width: 50%;
        float: left;
        margin-top: 10px;
        padding-left: 5px;
        padding-right: 5px;
    }

    .photos__img {
        height: 200px;
        width: 100%;
    }

    .photos__content {
        width: 100%;
    }

    /* Section Title */
    .section-title {
        font-size: 15pt;
        font-weight: 500;
        margin: 30px 0;
    }

    /* Page break */
    .page-breaker {
        page-break-after: always;
        height: 0;
    }

    /* Semi-table */
    .semi-table {
        margin: 30px 0;
        page-break-inside: avoid;
        border-spacing: 0 10px;
    }

    .semi-table__prop {
        width: 200px;
        padding-right: 10px;
    }

    .semi-table  tr td {
        height: 24px;
        vertical-align: top;
        line-height: 24px;
    }

    /* Tables */
    .divTable {
        display: table;
        width: 100%;
        border-collapse: collapse;
        margin: 20px -15px;
        padding: 0 20px;
    }

    .divTableBody {
        display: table-row-group;
    }

    .divTableRow {
        display: table-row;
    }

    .divTableCell {
        display: table-cell;
        vertical-align: top;
        padding: 4px 10px;
        width: 50%;
    }

    table {
        width: 100%;
    }

    .divTableCell tr {
        width: 100%;
    }

    .divTableCell tr td {
        width: 50%;
        vertical-align: top;
    }

    /* Disclaimer */
    .disclaimer {
        page-break-before: always;
        page-break-after: avoid;
    }

    .disclaimer__title {
        font-size: 8pt;
    }
</style>
