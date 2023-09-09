import { useCallback, useEffect } from "react";
import TreeNode from "./TreeNode";
import { DataNode, useTreeState } from "./TreeState";
import AddButton from "./AddButton";

type Props = {
  data: DataNode[];
};

const Tree = ({ data }: Props) => {
  const { folders, setFolders, addFolder } = useTreeState();

  useEffect(() => {
    setFolders(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const addFolderPrompt = useCallback(() => {
    const folderName = prompt("Bitte Namen für neuen Ordner eingeben:");
    if (folderName === null) {
      return;
    }

    if (!folderName) {
      alert("Fehler: Es wurde kein Ordnername eingegeben.");
      return;
    }

    addFolder(folderName);
  }, [addFolder]);

  return (
    <>
      <ul className="tree">
        {folders.map((node) => (
          <TreeNode key={node.id} node={node} />
        ))}
      </ul>
      <AddButton onClick={() => addFolderPrompt()} />
      <pre>{JSON.stringify(folders, null, 2)}</pre>
    </>
  );
};

export default Tree;
