@section('react')
        <!-- Note: when deploying, replace "development.js" with "production.min.js". -->
        <script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
		<script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>
		<!-- Load our React component. -->
		<script src="templates/{{$themedir}}/js/index.js"></script>
@show