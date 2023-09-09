import "./App.css";
import Tree from "./Tree/Tree";

function App() {
  const data = [
    {
      id: 1,
      name: "Parent 1",
      children: [
        {
          id: 2,
          name: "Child 1.1",
          children: [
            {
              id: 3,
              name: "Grandchild 1.1.1",
              children: [],
            },
            {
              id: 4,
              name: "Grandchild 1.1.2",
              children: [],
            },
          ],
        },
      ],
    },
    {
      id: 5,
      name: "Parent 2",
      children: [
        {
          id: 6,
          name: "Child 2.1",
          children: [
            {
              id: 7,
              name: "Grandchild 2.1.1",
              children: [],
            },
            {
              id: 8,
              name: "Grandchild 2.1.2",
              children: [],
            },
          ],
        },
      ],
    },
    {
      id: 9,
      name: "Parent 3",
      children: [],
    },
  ];
  return <Tree data={data} />;
}

export default App;
